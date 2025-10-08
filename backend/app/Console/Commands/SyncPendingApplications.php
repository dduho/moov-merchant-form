<?php

namespace App\Console\Commands;

use App\Models\MerchantApplication;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPendingApplications extends Command
{
    protected $signature = 'applications:sync-pending
                            {--limit=50 : Nombre maximum à traiter}
                            {--dry-run : Simulation}';
    
    protected $description = 'Synchronise les candidatures en attente';
    
    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }
    
    public function handle(): int
    {
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');
        
        $this->info("🔄 Synchronisation des candidatures...");
        
        $pending = MerchantApplication::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->limit($limit)
            ->get();
        
        if ($pending->isEmpty()) {
            $this->info("✅ Aucune candidature en attente.");
            return Command::SUCCESS;
        }
        
        $this->info("📋 {$pending->count()} candidature(s) trouvée(s).");
        
        $processed = 0;
        $errors = 0;
        
        foreach ($pending as $application) {
            try {
                if (!$dryRun) {
                    $application->updateStatus('under_review', 'Examen automatique démarré');
                    $this->notificationService->sendStatusUpdate($application);
                }
                
                $this->line("✅ {$application->reference_number} - {$application->full_name}");
                $processed++;
            } catch (\Exception $e) {
                $this->error("❌ Erreur: {$e->getMessage()}");
                Log::error("Erreur sync", ['id' => $application->id, 'error' => $e->getMessage()]);
                $errors++;
            }
        }
        
        $this->info("\n📊 Résumé: {$processed} traitées, {$errors} erreurs");
        
        if ($dryRun) {
            $this->warn("🔍 Mode simulation - Aucune modification");
        }
        
        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}