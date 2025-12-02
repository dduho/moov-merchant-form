<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeographicHierarchy extends Model
{
    use HasFactory;

    protected $table = 'geographic_hierarchy';

    protected $fillable = [
        'type',
        'name',
        'code',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Types constants
    public const TYPE_REGION = 'region';
    public const TYPE_PREFECTURE = 'prefecture';
    public const TYPE_COMMUNE = 'commune';
    public const TYPE_CANTON = 'canton';
    public const TYPE_VILLE = 'ville';

    /**
     * Get the parent location.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(GeographicHierarchy::class, 'parent_id');
    }

    /**
     * Get the children locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(GeographicHierarchy::class, 'parent_id');
    }

    /**
     * Scope for regions.
     */
    public function scopeRegions($query)
    {
        return $query->where('type', self::TYPE_REGION);
    }

    /**
     * Scope for prefectures.
     */
    public function scopePrefectures($query)
    {
        return $query->where('type', self::TYPE_PREFECTURE);
    }

    /**
     * Scope for communes.
     */
    public function scopeCommunes($query)
    {
        return $query->where('type', self::TYPE_COMMUNE);
    }

    /**
     * Scope for cantons.
     */
    public function scopeCantons($query)
    {
        return $query->where('type', self::TYPE_CANTON);
    }

    /**
     * Scope for villes.
     */
    public function scopeVilles($query)
    {
        return $query->where('type', self::TYPE_VILLE);
    }

    /**
     * Scope for active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get children by parent ID.
     */
    public static function getChildrenByParent($parentId)
    {
        return static::where('parent_id', $parentId)->active()->orderBy('name')->get();
    }

    /**
     * Get all regions with their full hierarchy.
     */
    public static function getFullHierarchy()
    {
        return static::regions()
            ->active()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('name')->with(['children' => function ($q) {
                    $q->active()->orderBy('name')->with(['children' => function ($q2) {
                        $q2->active()->orderBy('name')->with(['children' => function ($q3) {
                            $q3->active()->orderBy('name');
                        }]);
                    }]);
                }]);
            }])
            ->orderBy('name')
            ->get();
    }
}
