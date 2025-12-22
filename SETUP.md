# POS System Setup Guide

## Prerequisites
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js and npm

## Installation Steps

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Configure Environment

Copy `.env.example` to `.env` if not already done:

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Install Laravel Breeze

```bash
php artisan breeze:install blade
```

This will create authentication views and controllers.

### 4. Publish Spatie Permission Configuration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 5. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This will:
- Create all database tables
- Create roles (Admin, Manager, Cashier)
- Create permissions
- Create sample users, categories, products, customers, and suppliers

### 6. Build Assets

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

### 7. Start the Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default Login Credentials

After running the seeder, you can login with:

**Admin:**
- Email: `admin@pos.com`
- Password: `password`

**Manager:**
- Email: `manager@pos.com`
- Password: `password`

**Cashier:**
- Email: `cashier@pos.com`
- Password: `password`

## Features Implemented

✅ Authentication with Laravel Breeze (Blade)
✅ Role-based access control with Spatie Permission
✅ Complete database structure with migrations
✅ Product management with categories
✅ Customer and Supplier management
✅ Point of Sale (POS) screen with AJAX
✅ Sales management with invoice generation
✅ Purchase management
✅ Expense tracking
✅ Reports (Sales, Products, Stock, Profit, Customer Due)
✅ 80mm thermal printer compatible invoice
✅ Stock management
✅ Low stock alerts
✅ Partial payment support
✅ Multiple payment methods (Cash, Card, Mobile)

## Project Structure

- **Models**: All Eloquent models with relationships
- **Controllers**: Full CRUD controllers with business logic
- **Form Requests**: Validation for all store/update actions
- **Views**: Bootstrap 5 based Blade templates
- **Routes**: RESTful routes with authentication middleware
- **Seeders**: Sample data for testing

## Notes

- The POS screen uses jQuery and AJAX for real-time cart updates
- All sales and purchases use database transactions
- Stock is automatically updated on sales and purchases
- Invoice numbers are auto-generated
- The invoice view is optimized for 80mm thermal printers

## Troubleshooting

If you encounter issues:

1. Clear cache: `php artisan cache:clear`
2. Clear config: `php artisan config:clear`
3. Re-run migrations: `php artisan migrate:fresh --seed`
4. Check file permissions: `chmod -R 775 storage bootstrap/cache`



