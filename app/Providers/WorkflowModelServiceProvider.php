<?php

namespace App\Providers;

use App\Workflow;
use App\Observers\WorkflowObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class WorkflowModelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (Schema::connection('platform')->hasTable('workflows')) Workflow::observe(WorkflowObserver::class);
    }
}
