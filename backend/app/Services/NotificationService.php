<?php

namespace App\Services;

use App\Models\MerchantApplication;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    protected string $adminEmail;
    protected ?string $smsApiUrl;
    protected ?string $smsApiKey;
    
    public function __construct()
    {
        $this->adminEmail = config('moov.admin_email', 'admin@moovmoney.com');
        $this->smsApiUrl = config('moov.sms_api_url');
        $this->smsApiKey = config('moov.sms_api_key');
    }
    
    public function sendConfirmations(MerchantApplication $application): void
    {
        // Email
        if ($application->email) {
            $this->sendConfirmationEmail($application);
        }
        
        // SMS
        $this->sendConfirmationSMS($application);
        
        // Notifier les admins
        $this->notifyAdmins($application);
    }
    
    protected function sendConfirmationEmail(MerchantApplication $application): void
    {
        try {
            Mail::send('emails.application-confirmation', [
                'application' => $application
            ], function ($message) use ($application) {
                $message->to($application->email, $application->full_name)
                        ->subject('Confirmation - Candidature Moov Money');
            });
            
            Log::info('Email confirmation envoyé', ['id' => $application->id]);
        } catch (\Exception $e) {
            Log::error('Erreur email', ['error' => $e->getMessage()]);
        }
    }
    
    protected function sendConfirmationSMS(MerchantApplication $application): void
    {
        $message = "Bonjour {$application->full_name}, votre candidature Moov Money (Réf: {$application->reference_number}) a été reçue. Nous vous contacterons sous 48h.";
        
        $this->sendSMS($application->phone, $message);
    }
    
    public function notifyNewApplication(MerchantApplication $application): void
    {
        $this->notifyAdmins($application);
    }
    
    protected function notifyAdmins(MerchantApplication $application): void
    {
        try {
            // Envoyer l'email admin
            Mail::send('emails.new-application-admin', [
                'application' => $application
            ], function ($message) use ($application) {
                $message->to($this->adminEmail)
                        ->subject("Nouvelle candidature - {$application->reference_number}");
            });
            
            // Créer des notifications en base de données pour tous les admins
            $adminUsers = User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->get();
            
            foreach ($adminUsers as $admin) {
                $this->createNotification(
                    $admin,
                    'new_application',
                    'Nouvelle candidature reçue',
                    "Une nouvelle candidature de {$application->full_name} a été soumise. Référence: {$application->reference_number}",
                    [
                        'application_id' => $application->id,
                        'applicant_name' => $application->full_name,
                        'reference_number' => $application->reference_number
                    ]
                );
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur notification admin', ['error' => $e->getMessage()]);
        }
    }
    
    public function sendStatusUpdate(MerchantApplication $application): void
    {
        $messages = [
            'under_review' => 'Votre candidature est en cours d\'examen.',
            'approved' => 'Félicitations! Votre candidature a été approuvée.',
            'rejected' => 'Votre candidature n\'a pas été retenue.',
            'needs_info' => 'Informations complémentaires nécessaires.',
        ];
        
        if (!isset($messages[$application->status])) return;
        
        $message = "Bonjour {$application->full_name}, " . $messages[$application->status] . 
                   " Réf: {$application->reference_number}";
        
        $this->sendSMS($application->phone, $message);
        
        if ($application->email) {
            $this->sendStatusEmailUpdate($application);
        }
    }
    
    protected function sendSMS(string $phone, string $message): void
    {
        if (!$this->smsApiUrl || !$this->smsApiKey) {
            Log::warning('Configuration SMS manquante');
            return;
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->smsApiKey,
                'Content-Type' => 'application/json'
            ])->post($this->smsApiUrl, [
                'to' => $phone,
                'message' => $message,
                'from' => 'MoovMoney'
            ]);
            
            if ($response->successful()) {
                Log::info('SMS envoyé', ['phone' => $phone]);
            } else {
                Log::error('Erreur SMS', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception SMS', ['error' => $e->getMessage()]);
        }
    }
    
    protected function sendStatusEmailUpdate(MerchantApplication $application): void
    {
        try {
            Mail::send('emails.status-update', [
                'application' => $application
            ], function ($message) use ($application) {
                $message->to($application->email, $application->full_name)
                        ->subject("Mise à jour - {$application->reference_number}");
            });
        } catch (\Exception $e) {
            Log::error('Erreur email statut', ['error' => $e->getMessage()]);
        }
    }

    // ============================================================
    // NOUVELLES MÉTHODES POUR LE SYSTÈME DE NOTIFICATIONS
    // ============================================================

    /**
     * Créer une notification pour un utilisateur
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $actionUrl = null,
        string $priority = 'normal'
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'priority' => $priority
        ]);
    }

    /**
     * Notifier l'approbation d'une candidature
     */
    public function notifyApplicationApproved(MerchantApplication $application): void
    {
        $title = 'Candidature approuvée ! 🎉';
        $message = "Votre candidature #{$application->reference_number} a été approuvée par notre équipe.";
        
        $data = [
            'application_id' => $application->id,
            'reference_number' => $application->reference_number,
            'status' => 'approved'
        ];

        // Si c'est un utilisateur enregistré
        if ($application->user_id && $application->user) {
            // Créer la notification
            $this->createNotification(
                $application->user,
                'application_approved',
                $title,
                $message,
                $data,
                "/applications/{$application->id}",
                'high'
            );

            // Envoyer l'email à l'utilisateur
            $this->sendApplicationStatusEmail($application, 'approved');
        } else {
            // Envoyer seulement un email pour les non-utilisateurs
            $this->sendApplicationStatusEmailDirect($application, 'approved');
        }

        Log::info('Application approved notification sent', [
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'email' => $application->email
        ]);
    }

    /**
     * Notifier le rejet d'une candidature
     */
    public function notifyApplicationRejected(MerchantApplication $application, string $reason = null): void
    {
        $title = 'Candidature à réviser 📝';
        $message = "Votre candidature #{$application->reference_number} nécessite des modifications.";
        
        if ($reason) {
            $message .= " Raison : {$reason}";
        }

        $data = [
            'application_id' => $application->id,
            'reference_number' => $application->reference_number,
            'status' => 'rejected',
            'reason' => $reason,
            'can_resubmit' => true
        ];

        // Si c'est un utilisateur enregistré
        if ($application->user_id && $application->user) {
            // Créer la notification avec action de modification
            $this->createNotification(
                $application->user,
                'application_rejected',
                $title,
                $message . " Vous pouvez modifier et resoumettre votre candidature.",
                $data,
                "/applications/{$application->id}/edit",
                'high'
            );

            // Envoyer l'email à l'utilisateur
            $this->sendApplicationStatusEmail($application, 'rejected', $reason);
        } else {
            // Envoyer seulement un email pour les non-utilisateurs
            $this->sendApplicationStatusEmailDirect($application, 'rejected', $reason);
        }

        Log::info('Application rejected notification sent', [
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'email' => $application->email,
            'reason' => $reason
        ]);
    }

    /**
     * Obtenir les notifications non lues d'un utilisateur
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        $notifications = $user->notifications()
            ->unread()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return \App\Http\Resources\NotificationResource::collection($notifications);
    }

    /**
     * Obtenir toutes les notifications d'un utilisateur
     */
    public function getAllNotifications(User $user, int $limit = 50)
    {
        $notifications = $user->notifications()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return \App\Http\Resources\NotificationResource::collection($notifications);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification, User $user): bool
    {
        if ($notification->user_id !== $user->id) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Envoyer un email de statut de candidature pour utilisateur enregistré
     */
    private function sendApplicationStatusEmail(MerchantApplication $application, string $status, string $reason = null): void
    {
        try {
            Mail::send('emails.application-status', [
                'application' => $application,
                'status' => $status,
                'reason' => $reason,
                'user' => $application->user
            ], function ($message) use ($application, $status) {
                $message->to($application->user->email, $application->user->first_name . ' ' . $application->user->last_name);
                $message->subject($status === 'approved' 
                    ? "Candidature approuvée - #{$application->reference_number}"
                    : "Candidature à réviser - #{$application->reference_number}"
                );
            });
        } catch (\Exception $e) {
            Log::error('Failed to send application status email to registered user', [
                'application_id' => $application->id,
                'user_id' => $application->user_id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer un email de statut de candidature directement (non-utilisateur)
     */
    private function sendApplicationStatusEmailDirect(MerchantApplication $application, string $status, string $reason = null): void
    {
        try {
            Mail::send('emails.application-status-direct', [
                'application' => $application,
                'status' => $status,
                'reason' => $reason
            ], function ($message) use ($application, $status) {
                $message->to($application->email, $application->full_name);
                $message->subject($status === 'approved' 
                    ? "Candidature approuvée - #{$application->reference_number}"
                    : "Candidature à réviser - #{$application->reference_number}"
                );
            });
        } catch (\Exception $e) {
            Log::error('Failed to send application status email directly', [
                'application_id' => $application->id,
                'email' => $application->email,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }
}