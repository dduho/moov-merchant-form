<?php

namespace App\Console\Commands;

use App\Models\MerchantApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateApplicationReport extends Command
{
    protected $signature = 'applications:report
                            {--period=month : Période (week|month|quarter|year)}
                            {--format=json : Format (json|csv)}
                            {--output= : Fichier de sortie}';
    
    protected $description = 'Génère un rapport des candidatures';
    
    public function handle(): int
    {
        $period = $this->option('period');
        $format = $this->option('format');
        
        $this->info("📊 Génération du rapport ({$period})...");
        
        $dateRange = $this->getDateRange($period);
        
        $applications = MerchantApplication::with('documents')
            ->whereBetween('created_at', $dateRange)
            ->get();
        
        $stats = $this->generateStatistics($applications);
        
        $this->displayStats($stats);
        
        if ($this->option('output')) {
            $this->saveReport($stats, $applications, $format);
        }
        
        return Command::SUCCESS;
    }
    
    protected function getDateRange(string $period): array
    {
        return match($period) {
            'week' => [now()->subWeek(), now()],
            'month' => [now()->subMonth(), now()],
            'quarter' => [now()->subMonths(3), now()],
            'year' => [now()->subYear(), now()],
            default => [now()->subMonth(), now()]
        };
    }
    
    protected function generateStatistics($applications): array
    {
        return [
            'total_applications' => $applications->count(),
            'by_status' => $applications->groupBy('status')->map->count(),
            'by_business_type' => $applications->groupBy('business_type')->map->count(),
            'completion_rate' => $applications->filter->hasRequiredDocuments()->count() 
                / max($applications->count(), 1) * 100,
            'foreign_applicants' => $applications->where('is_foreigner', true)->count(),
            'with_cfe' => $applications->where('has_cfe', true)->count(),
            'with_nif' => $applications->where('has_nif', true)->count(),
        ];
    }
    
    protected function displayStats(array $stats): void
    {
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total', $stats['total_applications']],
                ['Taux complétude', round($stats['completion_rate'], 2) . '%'],
                ['Étrangers', $stats['foreign_applicants']],
                ['Avec CFE', $stats['with_cfe']],
                ['Avec NIF', $stats['with_nif']],
            ]
        );
    }
    
    protected function saveReport(array $stats, $applications, string $format): void
    {
        $filename = $this->option('output') ?: 'report_' . date('Y-m-d_H-i-s') . '.' . $format;
        
        $data = match($format) {
            'json' => json_encode([
                'generated_at' => now()->toISOString(),
                'statistics' => $stats,
                'applications' => $applications
            ], JSON_PRETTY_PRINT),
            'csv' => $this->convertToCsv($applications),
            default => json_encode($stats, JSON_PRETTY_PRINT)
        };
        
        Storage::put("reports/{$filename}", $data);
        
        $this->info("💾 Rapport sauvegardé: storage/app/reports/{$filename}");
    }
    
    protected function convertToCsv($applications): string
    {
        $csv = "Reference,Nom,Telephone,Email,Commerce,Type,Statut,Date\n";
        
        foreach ($applications as $app) {
            $csv .= implode(',', [
                $app->reference_number,
                '"' . $app->full_name . '"',
                $app->phone,
                $app->email ?? '',
                '"' . $app->business_name . '"',
                $app->business_type,
                $app->status,
                $app->created_at->format('Y-m-d H:i:s')
            ]) . "\n";
        }
        
        return $csv;
    }
}