<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('approvals:expire')->hourly();
            $schedule->command('sanctions:lift-expired')->hourly();
            $schedule->command('events:send-reminders')->hourly();
            $schedule->command('events:send-artist-hour-reminders')->everyMinute();
        });
    }
}
