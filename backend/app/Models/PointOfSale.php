<?php

namespace App\Models;

use App\Services\ProximityAlertService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class PointOfSale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'point_of_sales';

    protected $fillable = [
        // Relations
        'organization_id',
        'created_by',
        'validated_by',
        
        // Status
        'status',
        
        // Informations Dealer
        'numero',
        'dealer_name',
        'numero_flooz',
        'shortcode',
        
        // Informations PDV
        'nom_point',
        'profil',
        'type_activite',
        
        // Informations du Gérant
        'firstname',
        'lastname',
        'date_of_birth',
        'gender',
        'id_description',
        'id_number',
        'id_expiry_date',
        'nationality',
        'profession',
        
        // Localisation Hiérarchique
        'region',
        'prefecture',
        'commune',
        'canton',
        'ville',
        'quartier',
        'localisation',
        
        // Coordonnées GPS
        'latitude',
        'longitude',
        'gps_accuracy',
        
        // Contacts
        'numero_proprietaire',
        'autre_contact',
        
        // Fiscalité
        'nif',
        'regime_fiscal',
        
        // Visibilité
        'support_visibilite',
        'etat_support',
        
        // Autres
        'numero_cagnt',
        
        // Validation
        'validated_at',
        'rejected_at',
        'rejection_reason',
        
        // Identifiants
        'uuid',
        'reference_number',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'id_expiry_date' => 'date',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'gps_accuracy' => 'decimal:2',
            'validated_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected $appends = ['status_label', 'full_name', 'full_address'];

    protected static function booted(): void
    {
        static::creating(function (self $pdv) {
            $pdv->uuid ??= Str::uuid();
            $pdv->reference_number ??= static::generateReferenceNumber();
        });
    }

    // Relations
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Accesseurs
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'pending' => 'En attente',
                'validated' => 'Validé',
                'rejected' => 'Rejeté',
                default => 'Inconnu'
            }
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->firstname} {$this->lastname}"
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', array_filter([
                $this->quartier,
                $this->ville,
                $this->commune,
                $this->prefecture,
                $this->region
            ]))
        );
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nom_point', 'like', "%{$term}%")
              ->orWhere('firstname', 'like', "%{$term}%")
              ->orWhere('lastname', 'like', "%{$term}%")
              ->orWhere('dealer_name', 'like', "%{$term}%")
              ->orWhere('reference_number', 'like', "%{$term}%")
              ->orWhere('numero_proprietaire', 'like', "%{$term}%");
        });
    }

    // Méthodes
    public static function generateReferenceNumber(): string
    {
        do {
            $timestamp = now()->format('ymd');
            $random = strtoupper(Str::random(6));
            $reference = "PDV{$timestamp}{$random}";
        } while (static::where('reference_number', $reference)->exists());

        return $reference;
    }

    public static function getRegions(): array
    {
        return [
            'MARITIME' => 'Maritime',
            'PLATEAUX' => 'Plateaux', 
            'CENTRALE' => 'Centrale',
            'KARA' => 'Kara',
            'SAVANES' => 'Savanes'
        ];
    }

    /**
     * Validate the PDV.
     */
    public function validate(?int $validatorId = null): bool
    {
        $this->status = 'validated';
        $this->validated_at = now();
        $this->validated_by = $validatorId;
        $this->rejection_reason = null;
        $this->rejected_at = null;
        
        return $this->save();
    }

    /**
     * Reject the PDV.
     */
    public function reject(string $reason, ?int $validatorId = null): bool
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->validated_by = $validatorId;
        $this->rejection_reason = $reason;
        $this->validated_at = null;
        
        return $this->save();
    }

    /**
     * Check if GPS accuracy is sufficient.
     */
    public function hasValidGpsAccuracy(): bool
    {
        $requiredAccuracy = SystemSetting::getRequiredGpsAccuracy();
        return $this->gps_accuracy !== null && $this->gps_accuracy <= $requiredAccuracy;
    }

    /**
     * Calculate distance to another point in meters using Haversine formula.
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return ProximityAlertService::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Find nearby PDVs within a given distance.
     */
    public function findNearby(float $distanceMeters = 300): array
    {
        $pdvs = static::validated()
            ->where('id', '!=', $this->id)
            ->get();

        return $pdvs->filter(function ($pdv) use ($distanceMeters) {
            return $this->distanceTo($pdv->latitude, $pdv->longitude) <= $distanceMeters;
        })->values()->all();
    }
}
