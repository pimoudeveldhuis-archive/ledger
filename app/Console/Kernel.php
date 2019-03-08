<?php

namespace App\Console;

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
        \App\Console\Commands\CleanupImports::class,
        \App\Console\Commands\ProcessImports::class,
        \App\Console\Commands\CacheUser::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('import:process')->everyMinute()->name('import_process')->withoutOverlapping();
        $schedule->command('import:cleanup')->everyMinute()->name('import_cleanup')->withoutOverlapping();
        $schedule->command('user:cache')->dailyAt('2:00')->name('user_cache')->withoutOverlapping();

        $schedule->call(function () {
            \Illuminate\Support\Facades\Cache::flush();
        })->monthlyOn(1, '3:00')->name('cache_flush')->withoutOverlapping();
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
