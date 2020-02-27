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
php artisan db:seed --class=AffiliatePermissionSeeder
php artisan db:seed --class=AddressPermissionSeeder
php artisan db:seed --class=LoanReceiptRoleSeeder
php artisan db:seed --class=LoanStateSeeder
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=LoanSeeder
php artisan db:seed --class=PaymentTypeSeeder
php artisan db:seed --class=TagSeeder
php artisan db:seed --class=LoanGlobalParameterSeeder
