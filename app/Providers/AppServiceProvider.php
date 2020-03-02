<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Observers\RecordObserver;
use App\Record;
use App\Observers\UserObserver;
use App\User;
use App\Observers\AffiliateObserver;
use App\Affiliate;
use App\Observers\RoleObserver;
use App\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap any application services.
    *
    * @return void
    */
    public function boot()
    {
        // Localization
        setlocale(LC_TIME, env('APP_LC_TIME', 'es_BO.utf8'));
        Carbon::setLocale(env('APP_LOCALE', 'es'));

        // Custom validations
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });

        // Observers
        Record::observe(RecordObserver::class);
        User::observe(UserObserver::class);
        Affiliate::observe(AffiliateObserver::class);
        Role::observe(RoleObserver::class);

        // Polymorphic relationships
        Relation::morphMap([
            'affiliates' => 'App\Affiliate',
            'spouses' => 'App\Spouse',
            'beneficiaries' => 'App\LoanBeneficiary',
            'users' => 'App\User',
            'roles' => 'App\Role',
            'loans' => 'App\Loan'
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
