<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Observers\RecordObserver;
use App\Record;

class AppServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap any application services.
    *
    * @return void
    */
    public function boot()
    {
        setlocale(LC_TIME, env('APP_LC_TIME', 'es_BO.utf8'));
        Carbon::setLocale(env('APP_LOCALE', 'es'));
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });
        Record::observe(RecordObserver::class);
        Relation::morphMap([
            'affiliates' => 'App\Affiliate'
        ]);
    }

    /**
    * Register any application services.
    *
    * @return void
    */
    public function register()
    {
        //
    }
}
