#!/bin/bash

echo "Installing required packages for POS System..."
echo ""

# Install Spatie Permission
echo "Installing spatie/laravel-permission..."
composer require spatie/laravel-permission

# Install Laravel Breeze
echo "Installing laravel/breeze..."
composer require laravel/breeze

# Install Excel package
echo "Installing maatwebsite/excel..."
composer require maatwebsite/excel

# Install PDF package
echo "Installing barryvdh/laravel-dompdf..."
composer require barryvdh/laravel-dompdf

echo ""
echo "Packages installed successfully!"
echo "Now run: php artisan vendor:publish --provider=\"Spatie\Permission\PermissionServiceProvider\""
echo "Then run: php artisan migrate --seed"






