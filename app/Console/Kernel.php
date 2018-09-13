<?php

namespace LevelV\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('token:refresh')->everyTenMinutes()->unlessBetween('10:45', '11:15');

        $schedule->command('update:attributes')->hourly()->unlessBetween('10:45', '11:15');
        $schedule->command('update:clones')->hourly()->unlessBetween('10:45', '11:15');
        $schedule->command('update:implants')->hourly()->unlessBetween('10:45', '11:15');
        $schedule->command('update:skillqueue')->hourly()->unlessBetween('10:45', '11:15');
        $schedule->command('update:skillz')->hourly()->unlessBetween('10:45', '11:15');

        $schedule->command('clean-stale-members')->weekly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
