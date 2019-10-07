<?php

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1',
], function () {
    Route::patch('picture/{id}', 'Api\V1\AffiliateController@picture_save');
    // Login
    Route::resource('auth', 'Api\V1\AuthController')->only(['store']);
    Route::resource('date', 'Api\V1\DateController')->only(['index']);
    // Affiliate
    Route::resource('affiliate', 'Api\V1\AffiliateController')->only(['show']);
    // City
    Route::resource('city', 'Api\V1\CityController')->only(['index']);
    // state
    Route::resource('affiliateState', 'Api\V1\AffiliateStateController')->only(['index']);
    Route::get('AffiliateState/{id}/affiliateStateType', 'Api\V1\AffiliateStateController@get_affiliateStateType');
    // Degree
    Route::resource('degree', 'Api\V1\DegreeController')->only(['index']);
    //pension Entity
    Route::resource('pensionEntity', 'Api\V1\PensionEntityController')->only(['index']);
    //category
    Route::resource('category', 'Api\V1\CategoryController');
    // Fingerprint
    Route::get('affiliate/{id}/fingerprint', 'Api\V1\AffiliateController@fingerprint_saved');
    Route::get('affiliate/{id}/fingerprint_picture', 'Api\V1\AffiliateController@FingerImageprint');
    Route::get('affiliate/{id}/profile_picture', 'Api\V1\AffiliateController@PictureImageprint');
    // Record
    Route::resource('record', 'Api\V1\RecordController')->only(['index']);
    // webcam
    //Route::resource('picture', 'Api\V1\AffiliateController');
    // With credentials
    Route::group([
        'middleware' => 'jwt.auth'
    ], function () {
        // Logout and refresh token
        Route::resource('auth', 'Api\V1\AuthController')->only(['show', 'update', 'destroy']);
        // Admin routes
        Route::group([
            'middleware' => 'role:admin'
        ], function () {
            // User
            Route::resource('user', 'Api\V1\UserController');
            Route::get('user/{id}/role', 'Api\V1\UserController@get_roles');
            Route::post('user/{id}/role', 'Api\V1\UserController@set_roles');
            Route::get('user/{id}/permission', 'Api\V1\UserController@get_permissions');
            // Ldap
            Route::get('ldap/unregistered', 'Api\V1\UserController@unregistered_users');
            Route::get('ldap/sync', 'Api\V1\UserController@synchronize_users');
            // Module
            Route::resource('module', 'Api\V1\ModuleController')->only(['index']);
            Route::get('module/{id}/role', 'Api\V1\ModuleController@get_roles');
            // Role
            Route::resource('role', 'Api\V1\RoleController')->only(['index', 'show']);
            Route::get('role/{id}/permission', 'Api\V1\RoleController@get_permissions');
            Route::post('role/{id}/permission', 'Api\V1\RoleController@set_permissions');
            // Permission
            Route::resource('permission', 'Api\V1\PermissionController')->only(['index']);
             // Affiliate
            Route::resource('affiliate', 'Api\V1\AffiliateController')->only(['index', 'store', 'update', 'destroy']);
            Route::patch('affiliate/{id}/fingerprint', 'Api\V1\AffiliateController@fingerprint_updated');
            
        });
    });
           
            //Route::resource('city', 'Api\V1\CityController')->only(['index', 'show']);        
            //index recordController
});