<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'username',
        'password',
        'is_active',
        'is_blocked',
        'must_change_password',
        'last_login_at',
        'password_changed_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function isCommercial()
    {
        return $this->hasRole('commercial');
    }

    public function isPersonnel()
    {
        return $this->hasRole('personnel');
    }

    public function canSubmitApplications()
    {
        return $this->hasRole('commercial') || $this->hasRole('personnel');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadNotificationsCount()
    {
        return $this->notifications()
            ->unread()
            ->notExpired()
            ->count();
    }

    /**
     * Relation avec les objectifs
     */
    public function objectives()
    {
        return $this->hasMany(UserObjective::class);
    }

    /**
     * Obtenir l'objectif actuel de l'utilisateur
     */
    public function currentObjective($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');
        
        return $this->objectives()
            ->active()
            ->forYear($year)
            ->forMonth($month)
            ->first();
    }

        /**
     * Relation avec les candidatures créées par l'utilisateur
     */
    public function merchantApplications()
    {
        return $this->hasMany(MerchantApplication::class, 'user_id');
    }

    /**
     * Compter les candidatures pour une période
     */
    public function countApplicationsForPeriod($startDate, $endDate)
    {
        return $this->merchantApplications()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Vérifier si l'utilisateur peut se connecter
     */
    public function canLogin()
    {
        return $this->is_active && !$this->is_blocked;
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($newPassword = 'password')
    {
        $this->password = bcrypt($newPassword);
        $this->must_change_password = true;
        $this->password_changed_at = null;
        $this->save();
    }

    /**
     * Marquer que l'utilisateur a changé son mot de passe
     */
    public function markPasswordAsChanged()
    {
        $this->must_change_password = false;
        $this->password_changed_at = now();
        $this->save();
    }

    /**
     * Appliquer les objectifs globaux à cet utilisateur
     */
    public function applyGlobalObjectives()
    {
        // Récupérer tous les objectifs globaux actifs
        $globalObjectives = UserObjective::whereNull('user_id')
            ->active()
            ->get();

        foreach ($globalObjectives as $globalObjective) {
            // Vérifier si l'utilisateur n'a pas déjà un objectif pour cette période
            $existingObjective = $this->objectives()
                ->where('target_year', $globalObjective->target_year)
                ->where('target_month', $globalObjective->target_month)
                ->first();

            if (!$existingObjective) {
                // Créer un objectif personnel basé sur l'objectif global
                $this->objectives()->create([
                    'monthly_target' => $globalObjective->monthly_target,
                    'yearly_target' => $globalObjective->yearly_target,
                    'target_year' => $globalObjective->target_year,
                    'target_month' => $globalObjective->target_month,
                    'description' => $globalObjective->description,
                    'is_active' => true
                ]);
            }
        }
    }
}
