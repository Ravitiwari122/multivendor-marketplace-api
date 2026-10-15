# TradeXon – E-commerce Backend

A REST API backend for a multi-vendor e-commerce platform built with Laravel 10 and MySQL.

### Features
- Authentication using Laravel Sanctum
- Role-based access for Admin, Seller and Customer
- Product and Category Management
- Cart and Wishlist APIs
- Checkout and Order Management
- Razorpay Payment Integration (Test Mode)

### Tech Stack
- PHP, Laravel 10
- MySQL
- Laravel Sanctum
- Razorpay
- Postman

### Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure your database and Razorpay test credentials in the `.env` file before running the project.
