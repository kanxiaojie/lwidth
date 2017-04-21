<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\PostConsole::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('post')->dailyAt('3:15');
        $schedule->command('post2')->dailyAt('6:15');
        $schedule->command('post2')->dailyAt('9:15');
        $schedule->command('post2')->dailyAt('12:15');
        $schedule->command('post2')->dailyAt('15:15');
        $schedule->command('post2')->dailyAt('18:15');
        $schedule->command('post2')->dailyAt('21:15');
        $schedule->command('post2')->dailyAt('23:55');

    }
}
