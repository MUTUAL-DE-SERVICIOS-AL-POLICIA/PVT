#!/usr/bin/env bash

# Migration
php artisan migrate --force

# Seeders
php artisan db:seed --class=CityGeolocationSeeder
php artisan db:seed --class=RecordTypeNameSeeder
php artisan db:seed --class=RecordTypeSistemaSeeder
php artisan db:seed --class=ModuleNameSeeder
php artisan db:seed --class=RoleNameSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=LoanPermissionSeeder
php artisan db:seed --class=UserPermissionSeeder
php artisan db:seed --class=RecordPermissionSeeder
php artisan db:seed --class=NotePermissionSeeder
php artisan db:seed --class=AffiliatePermissionSeeder
php artisan db:seed --class=SettingPermissionSeeder
php artisan db:seed --class=AdvanceRoleSeeder
php artisan db:seed --class=LoanStateSeeder
php artisan db:seed --class=LoanPaymentStateSeeder
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=LoanStructureSeeder
php artisan db:seed --class=PaymentTypeSeeder
php artisan db:seed --class=ProcedureAmortizationSeeder
php artisan db:seed --class=RoleSequenceSeeder
php artisan db:seed --class=LoanGlobalParameterSeeder
php artisan db:seed --class=VoucherTypeSeeder
php artisan db:seed --class=TagSeeder
php artisan db:seed --class=LoanDestinySeeder
php artisan db:seed --class=LoanDestinyProcedureTypeSeeder
php artisan db:seed --class=ObservationTypeSeeder






