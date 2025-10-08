<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SyncPendingApplications::class,
        Commands\GenerateApplicationReport::class,
    ];
    
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('applications:sync-pending')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/sync.log'));
        
        $schedule->command('applications:report --period=week --format=json')
                 ->weeklyOn(1, '09:00')
                 ->emailOutputTo(config('moov.admin_email'));
    }
    
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
    }
}