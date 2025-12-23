# Package Installation Instructions

Due to network connectivity issues, please install the required packages manually:

## Step 1: Install Required Packages

Run these commands one by one (wait for each to complete):

```bash
composer require spatie/laravel-permission
composer require laravel/breeze
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

Or use the provided script:
```bash
./install-packages.sh
```

## Step 2: Publish Spatie Permission Configuration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## Step 3: Install Laravel Breeze

```bash
php artisan breeze:install blade
```

## Step 4: Run Migrations and Seeders

```bash
php artisan migrate --seed
```

## Alternative: If Network Issues Persist

If you continue to have network issues, you can:

1. **Use a different Composer repository mirror:**
   ```bash
   composer config -g repo.packagist composer https://packagist.phpcomposer.com
   ```

2. **Or use a VPN/proxy** to access packagist.org

3. **Or download packages manually** and install from local files

## Note

The seeder has been updated to check if Spatie Permission is installed. If the package is not found, it will show an error message instead of crashing.




