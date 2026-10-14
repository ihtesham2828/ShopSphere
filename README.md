# ShopSphere — PHP + MySQL E-commerce Starter

A runnable, beginner-friendly full-stack e-commerce project built with **PHP 8.1+**, **MySQL 8+**, HTML, CSS, and vanilla JavaScript.

## Features

- Customer registration, login, logout
- Password hashing and session-based authentication
- Product listing, search, category filtering, product details
- Session-based shopping cart with quantity updates
- Checkout form and order creation
- Database transaction when creating orders and reducing stock
- Customer order history
- Admin dashboard with product CRUD and order status management
- CSRF tokens on state-changing forms
- PDO prepared statements
- Basic validation and output escaping
- Responsive layout
- `.env.example` configuration template

> This is a learning/portfolio project. Before using it for a real business, add production-grade security review, HTTPS, email verification/password reset, logging, rate limiting, tax/shipping rules, and a real payment provider. Checkout currently records **cash-on-delivery/demo orders**; it does not collect card details or process online payments.

## Requirements

- PHP 8.1 or newer
- MySQL 8+ or MariaDB
- Apache/Nginx, or PHP's built-in development server
- PHP extensions: `pdo_mysql`
- Optional: XAMPP (easy local setup on Windows)

## 1. Put the project in your web directory

For XAMPP, extract the project folder to:

`C:\xampp\htdocs\php-mysql-ecommerce`

The project should contain `index.php` directly inside that folder.

## 2. Create the database

Open phpMyAdmin at `http://localhost/phpmyadmin`, create a database named:

`shopsphere`

Then import `database/schema.sql`.

The SQL creates the tables and adds sample categories/products. It does **not** create an admin password automatically, because passwords must be hashed by PHP.

Alternative from a terminal:

```bash
mysql -u root -p < database/schema.sql
```

If your local MySQL root user has no password (common in default XAMPP installs), press Enter when prompted.

## 3. Configure database access

Copy `.env.example` to `.env` and update the values:

```env
APP_NAME="ShopSphere"
APP_URL="http://localhost/php-mysql-ecommerce"
APP_ENV="local"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="shopsphere"
DB_USER="root"
DB_PASS=""
```

The app includes a small `.env` reader and does not need Composer. If `.env` is absent, the example defaults are used. **Do not commit `.env` to a public repository.**

For XAMPP, `DB_USER=root` and an empty `DB_PASS` are common defaults. Use your own credentials if different.

## 4. Run the project

### Option A — XAMPP

1. Start Apache and MySQL in XAMPP Control Panel.
2. Visit `http://localhost/php-mysql-ecommerce/`.

### Option B — PHP development server

From the project root:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`.

Make sure MySQL is running and the database is imported first.

## 5. Create an admin account

1. Register a normal account through the website.
2. Open phpMyAdmin → `shopsphere` → `users`.
3. Find your user row and copy its `id`.
4. Run this query, replacing `1` with your user ID:

```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

5. Log out and log back in. You should now see the **Admin** link in the navigation.

Never expose admin access to untrusted users.

## Project structure

```text
php-mysql-ecommerce/
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── bootstrap.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── admin/
│   ├── index.php
│   ├── products.php
│   ├── product-form.php
│   └── orders.php
├── .env.example
├── .gitignore
├── index.php
├── product.php
├── cart.php
├── checkout.php
├── register.php
├── login.php
├── logout.php
├── account.php
└── order-success.php
```

## Notes

- Product images use remote Unsplash URLs for demo presentation. Replace them with images you own or have permission to use for your portfolio.
- Prices are stored as `DECIMAL(10,2)`.
- Stock is checked again during checkout inside a transaction.
- Order statuses are `pending`, `processing`, `shipped`, and `cancelled`.
- No online card-payment flow is included. Do not add raw card fields to this project.
