<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users belonging to this organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the points of sale belonging to this organization.
     */
    public function pointsOfSale(): HasMany
    {
        return $this->hasMany(PointOfSale::class);
    }

    /**
     * Scope for active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Generate a unique code for the organization.
     */
    public static function generateCode(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $random = strtoupper(\Illuminate\Support\Str::random(4));
        return "{$prefix}{$random}";
    }
}
