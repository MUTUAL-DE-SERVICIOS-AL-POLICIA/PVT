#!/usr/bin/env sh

# Migration
php artisan migrate

# Seeders
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=UserPermissionSeeder
php artisan db:seed --class=RecordPermissionSeeder
php artisan db:seed --class=AffiliatePermissionSeeder
php artisan db:seed --class=LoanReceiptRoleSeeder
php artisan db:seed --class=AdminSeeder