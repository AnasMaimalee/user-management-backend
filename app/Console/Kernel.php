<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 🧾 Loan repayment (monthly)
        $schedule->command('loans:repay')
            ->monthlyOn(1, '00:00');

        // 🖐 ZKTeco attendance sync (every 5 minutes)
        $schedule->command('zk:sync-attendance')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('attendance:process-daily')
            ->dailyAt('01:00')
            ->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }



}
