<?php
namespace App\Providers;

use App\Spouse;
use App\Observers\SpouseObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SpouseServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if (Schema::connection('platform')->hasTable('spouses')) Spouse::observe(SpouseObserver::class);
    }
}
