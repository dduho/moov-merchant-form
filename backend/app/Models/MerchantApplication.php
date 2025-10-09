<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class MerchantApplication extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        // Relation utilisateur
        'user_id',
        
        // Informations personnelles
        'first_name',
        'last_name',
        'full_name',
        'birth_date',
        'birth_place',
        'gender',
        'nationality',
        'phone',
        'merchant_phone',
        'email',
        'address',
        
        // Documents d'identité
        'id_type',
        'id_number',
        'id_expiry_date',
        'has_anid_card',
        'anid_number',
        'anid_expiry_date',
        'is_foreigner',
        
        // Informations commerciales
        'business_name',
        'business_type',
        'business_phones',
        'business_email',
        'business_address',
        'usage_type',
        'has_cfe',
        'cfe_number',
        'cfe_expiry_date',
        'has_nif',
        'nif_number',
        
        // Localisation
        'latitude',
        'longitude',
        'location_accuracy',
        'location_description',
        'shop_address',
        'shop_city',
        
        // Signature et validation
        'signature',
        'accept_terms',
        
        // Statut
        'status',
        'admin_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejected_reason',
        
        // Identifiants techniques
        'uuid',
        'reference_number',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'id_expiry_date' => 'date',
            'anid_expiry_date' => 'date',
            'cfe_expiry_date' => 'date',
            'has_anid_card' => 'boolean',
            'is_foreigner' => 'boolean',
            'has_cfe' => 'boolean',
            'has_nif' => 'boolean',
            'business_phones' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'location_accuracy' => 'decimal:2',
            'accept_terms' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected $hidden = ['signature'];
    
    protected $appends = ['status_label', 'business_type_label', 'usage_type_label', 'id_type_label'];

    protected static function booted(): void
    {
        static::creating(function (self $application) {
            // Générer full_name à partir de first_name et last_name
            if ($application->first_name && $application->last_name) {
                $application->full_name = "{$application->first_name} {$application->last_name}";
            }
            
            $application->reference_number = static::generateReferenceNumber();
            $application->submitted_at ??= now();
            $application->uuid ??= Str::uuid();
        });
        
        static::updating(function (self $application) {
            // Mettre à jour full_name si first_name ou last_name change
            if ($application->isDirty(['first_name', 'last_name'])) {
                $application->full_name = "{$application->first_name} {$application->last_name}";
            }
        });
    }

    // Relations
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Accesseurs
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'pending' => 'En attente',
                'under_review' => 'En cours d\'examen',
                'approved' => 'Approuvée',
                'rejected' => 'Rejetée',
                'needs_info' => 'Informations manquantes',
                'archived' => 'Archivée',
                default => 'Inconnu'
            }
        );
    }

    protected function businessTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->business_type) {
                'boutique' => 'Boutique générale',
                'pharmacie' => 'Pharmacie',
                'station-service' => 'Station service',
                'supermarche' => 'Supermarché',
                'autre' => 'Autre',
                default => 'Non spécifié'
            }
        );
    }

    protected function usageTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->usage_type) {
                'TRADER' => 'Trader',
                'MERC' => 'Marchand',
                'TRADERWNIF' => 'Trader avec NIF',
                'CORP' => 'Corporate',
                default => 'Non spécifié'
            }
        );
    }

    protected function idTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->id_type) {
                'cni' => 'Carte Nationale d\'Identité',
                'passport' => 'Passeport',
                'residence' => 'Carte de séjour',
                default => 'Non spécifié'
            }
        );
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('business_name', 'like', "%{$term}%")
              ->orWhere('reference_number', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('merchant_phone', 'like', "%{$term}%");
        });
    }

    // Méthodes
    public static function generateReferenceNumber(): string
    {
        do {
            $timestamp = now()->format('ymd');
            $random = strtoupper(Str::random(6));
            $reference = "MM{$timestamp}{$random}";
        } while (static::where('reference_number', $reference)->exists());

        return $reference;
    }

    public function hasRequiredDocuments(): bool
    {
        $requiredDocs = ['id_card'];

        if ($this->has_anid_card) $requiredDocs[] = 'anid_card';
        if ($this->is_foreigner) $requiredDocs[] = 'residence_card';

        $uploadedDocs = $this->documents->pluck('document_type')->toArray();

        return empty(array_diff($requiredDocs, $uploadedDocs));
    }

    public function updateStatus(string $status, ?string $notes = null, ?int $reviewerId = null): bool
    {
        $this->status = $status;
        $this->admin_notes = $notes;
        $this->reviewed_at = now();
        $this->reviewed_by = $reviewerId;

        return $this->save();
    }
}