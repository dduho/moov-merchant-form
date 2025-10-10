<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monthly_target',
        'yearly_target', 
        'target_year',
        'target_month',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_year' => 'integer',
        'target_month' => 'integer',
        'monthly_target' => 'integer',
        'yearly_target' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les objectifs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour une année donnée
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('target_year', $year);
    }

    /**
     * Scope pour un mois donné
     */
    public function scopeForMonth($query, $month)
    {
        return $query->where('target_month', $month);
    }
}