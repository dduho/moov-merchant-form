<?php

namespace App\Services;

use App\Models\MerchantApplication;
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
    
    protected function notifyAdmins(MerchantApplication $application): void
    {
        try {
            Mail::send('emails.new-application-admin', [
                'application' => $application
            ], function ($message) use ($application) {
                $message->to($this->adminEmail)
                        ->subject("Nouvelle candidature - {$application->reference_number}");
            });
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
}