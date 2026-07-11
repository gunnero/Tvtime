<?php

namespace App\Providers;

use App\Services\OperationalMonitoringService;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(OperationalMonitoringService $monitoring): void
    {
        Queue::failing(fn (JobFailed $event) => $monitoring->recordFailedJob($event));
    }
}
