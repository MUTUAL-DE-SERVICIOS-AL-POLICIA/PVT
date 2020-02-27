<?php

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1',
], function () {
    Route::resource('global_parameter', 'Api\V1\LoanGlobalParameterController')->only('store');
    Route::resource('global_parameter', 'Api\V1\LoanGlobalParameterController')->only('index', 'show');
    Route::resource('global_parameter', 'Api\V1\LoanGlobalParameterController')->only('update');
    Route::resource('global_parameter', 'Api\V1\LoanGlobalParameterController')->only('destroy');

    // Rutas abiertas
    Route::resource('auth', 'Api\V1\AuthController')->only('store');
    Route::resource('config', 'Api\V1\ConfigController')->only('index');
    Route::resource('affiliate', 'Api\V1\AffiliateController')->only('show');
    Route::resource('city', 'Api\V1\CityController')->only('index');
    Route::resource('affiliate_state', 'Api\V1\AffiliateStateController')->only('index');
    Route::resource('degree', 'Api\V1\DegreeController')->only('index');
    Route::resource('pension_entity', 'Api\V1\PensionEntityController')->only('index', 'show');
    Route::resource('category', 'Api\V1\CategoryController')->only('index');
    Route::resource('procedure_type', 'Api\V1\ProcedureTypeController')->only('index');
    Route::resource('procedure_modality', 'Api\V1\ProcedureModalityController')->only('index', 'show');
    Route::resource('module', 'Api\V1\ModuleController')->only('index', 'show');
    Route::get('module/{id}/procedure_type', 'Api\V1\ModuleController@get_procedure_types');
    // Biométrico
    Route::get('affiliate/{id}/fingerprint', 'Api\V1\AffiliateController@fingerprint_saved');


    // INDEFINIDO (TODO)
    //webcam
    Route::patch('picture/{id}', 'Api\V1\AffiliateController@picture_save');
    // Fingerprint
    //document
    Route::get('document/{affiliate_id}', 'Api\V1\ScannedDocumentController@create_document');
    Route::resource('procedureDocument', 'Api\V1\ProcedureDocumentController')->only('index');


    // Autenticado con token
    Route::group([
        'middleware' => 'auth'
    ], function () {
        Route::resource('auth', 'Api\V1\AuthController')->only('index');
        Route::patch('auth', 'Api\V1\AuthController@refresh');
        Route::delete('auth', 'Api\V1\AuthController@logout');
        Route::get('procedure_modality/{id}/requirements', 'Api\V1\ProcedureModalityController@get_requirements');
        Route::resource('calculator', 'Api\V1\CalculatorController')->only('store');
        Route::resource('role', 'Api\V1\RoleController')->only('index', 'show');

        // Afiliado
        Route::group([
            'middleware' => 'permission:show-affiliate'
        ], function () {
            Route::resource('affiliate', 'Api\V1\AffiliateController')->only('index');
            Route::resource('spouse', 'Api\V1\SpouseController')->only('index', 'show');
            Route::get('affiliate/{id}/degree', 'Api\V1\AffiliateController@get_degree');
            Route::get('affiliate/{id}/category', 'Api\V1\AffiliateController@get_category');
            Route::get('affiliate/{id}/unit', 'Api\V1\AffiliateController@get_unit');
            Route::get('affiliate/{id}/state', 'Api\V1\AffiliateController@get_state');
            Route::get('affiliate/{id}/spouse', 'Api\V1\AffiliateController@get_spouse');
            Route::get('affiliate/{id}/address', 'Api\V1\AffiliateController@get_addresses');
            Route::get('affiliate/{id}/contribution', 'Api\V1\AffiliateController@get_contributions');
            Route::get('affiliate/{id}/fingerprint_picture', 'Api\V1\AffiliateController@get_fingerprint_images');
            Route::get('affiliate/{id}/profile_picture', 'Api\V1\AffiliateController@get_profile_images');
        });
        Route::group([
            'middleware' => 'permission:create-affiliate'
        ], function () {
            Route::resource('affiliate', 'Api\V1\AffiliateController')->only('store');
        });
        Route::group([
            'middleware' => 'permission:update-affiliate-primary|update-affiliate-secondary'
        ], function () {
            Route::resource('affiliate', 'Api\V1\AffiliateController')->only('update');
            Route::resource('spouse', 'Api\V1\SpouseController')->only('update');
        });
        Route::group([
            'middleware' => 'permission:update-affiliate-secondary'
        ], function () {
            Route::resource('spouse', 'Api\V1\SpouseController')->only('store');
            Route::patch('affiliate/{id}/fingerprint', 'Api\V1\AffiliateController@update_fingerprint');
            Route::patch('affiliate/{id}/address', 'Api\V1\AffiliateController@update_addresses');
            Route::resource('beneficiary', 'Api\V1\LoanBeneficiaryController')->only('index', 'store', 'show', 'destroy', 'update');
        });
        Route::group([
            'middleware' => 'permission:delete-affiliate'
        ], function () {
            Route::resource('affiliate', 'Api\V1\AffiliateController')->only('destroy');
            Route::resource('spouse', 'Api\V1\SpouseController')->only('destroy');
        });

        // Préstamo
        Route::group([
            'middleware' => 'permission:show-loan'
        ], function () {
            Route::resource('loan', 'Api\V1\LoanController')->only('index');
            Route::resource('loan', 'Api\V1\LoanController')->only('show');
            Route::get('loan/{id}/disbursable', 'Api\V1\LoanController@get_disbursable');
            Route::resource('loan_interval', 'Api\V1\LoanIntervalController')->only('index');
            Route::get('affiliate/{id}/loan','Api\V1\AffiliateController@get_loans');
        });
        Route::group([
            'middleware' => 'permission:create-loan'
        ], function () {
            Route::resource('loan', 'Api\V1\LoanController')->only('store');
            Route::post('loan/{id}/document', 'Api\V1\LoanController@submit_documents');
            Route::get('loan/print/requirements', 'Api\V1\LoanController@print_requirements');
            Route::get('affiliate/{id}/loan_modality', 'Api\V1\AffiliateController@get_loan_modality');
            Route::get('loan/print/form', 'Api\V1\LoanController@print_form');
            Route::get('loan/{id}/print/contract', 'Api\V1\LoanController@print_contract');
        });
        Route::group([
            'middleware' => 'permission:update-loan'
        ], function () {
            Route::resource('loan', 'Api\V1\LoanController')->only('update');
        });
        Route::group([
            'middleware' => 'permission:delete-loan'
        ], function () {
            Route::resource('loan', 'Api\V1\LoanController')->only('destroy');
        });

        // Dirección
        Route::group([
            'middleware' => 'permission:create-address'
        ], function () {
            Route::resource('address', 'Api\V1\AddressController')->only('store');
        });
        Route::group([
            'middleware' => 'permission:update-address'
        ], function () {
            Route::resource('address', 'Api\V1\AddressController')->only('update');
        });
        Route::group([
            'middleware' => 'permission:delete-address'
        ], function () {
            Route::resource('address', 'Api\V1\AddressController')->only('destroy');
        });

        // Historial de actividad
        Route::group([
            'middleware' => 'permission:show-record'
        ], function () {
            Route::resource('record', 'Api\V1\RecordController')->only('index');
        });

        // Administrador
        Route::group([
            'middleware' => 'role:TE-admin'
        ], function () {
            // Usuario
            Route::resource('user', 'Api\V1\UserController');
            Route::get('user/{id}/role', 'Api\V1\UserController@get_roles');
            Route::post('user/{id}/role', 'Api\V1\UserController@set_roles');
            Route::get('user/{id}/permission', 'Api\V1\UserController@get_permissions');
            // Ldap
            if (env("LDAP_AUTHENTICATION")) {
                Route::get('user/ldap/unregistered', 'Api\V1\UserController@unregistered_users');
                Route::get('user/ldap/sync', 'Api\V1\UserController@synchronize_users');
            }
            // Módulo
            Route::get('module/{id}/role', 'Api\V1\ModuleController@get_roles');
            Route::get('role/{id}/permission', 'Api\V1\RoleController@get_permissions');
            Route::post('role/{id}/permission', 'Api\V1\RoleController@set_permissions');
            // Permiso
            Route::resource('permission', 'Api\V1\PermissionController')->only('index');
        });
    });
});
