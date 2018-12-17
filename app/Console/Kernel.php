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
        //
        //Commands\updateLeadsToAlpha::class,
        Commands\updateLeadsCallToAlpha::class,
        Commands\updateLeadsDirectToAlpha::class,
        Commands\updateLeadsFormToAlpha::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
        /*
        $schedule->command('command:updateLeadsToAlpha')
        ->timezone('Asia/Bangkok')
        ->dailyAt('12:00');
        */
        
        
        $schedule->command('command:updateLeadsCallToAlpha')
        ->timezone('Asia/Bangkok')
        ->hourlyAt(5);
        
        $schedule->command('command:updateLeadsDirectToAlpha')
        ->timezone('Asia/Bangkok')
        ->hourlyAt(0);
        
        $schedule->command('command:updateLeadsFormToAlpha')
        ->timezone('Asia/Bangkok')
        ->hourlyAt(30);
        
    }
}
