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
    protected $commands = [];

    /**
    * Define the application's command schedule.
    *
    * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
    * @return void
    */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            app()->call([app()->make('App\Http\Controllers\Api\V1\LoanController'), 'switch_states']);
        })->daily();
        $schedule->call(function () {
            app()->call([app()->make('App\Http\Controllers\Api\V1\LoanPaymentController'), 'deleteCanceledPaymentRecord']);
        })->daily();
        $schedule->call(function () {
            app()->call([app()->make('App\Http\Controllers\Api\V1\LoanController'), 'switch_loans_guarantors']);
        })->everyMinute();
    }

    /**
    * Register the commands for the application.
    *
    * @return void
    */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}