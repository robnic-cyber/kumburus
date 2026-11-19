# Kumburus

Kumburus is a Laravel hotel booking system with authentication, hotel and room CRUD, transaction-safe reservations, and booking confirmation email notifications.

## Features

- Guest registration, login, logout, and session authentication.
- Public hotel and room browsing with city search.
- Admin-only hotel and room CRUD with archive actions.
- Booking creation inside a database transaction with row locking and overlapping-date protection.
- Booking cancellation before check-in.
- Confirmation email mailable. The local default uses Laravel's `log` mailer, so no external credentials are needed for development.
- SQLite by default, with migrations and seed data included.

## Local setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open `http://127.0.0.1:8000`.

Demo accounts:

- Admin: `admin@kumburus.test` / `password`
- Guest: `guest@kumburus.test` / `password`

## Email configuration

For local development, keep `MAIL_MAILER=log`. Confirmation emails are written to `storage/logs/laravel.log`.

For SMTP, set the `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME` values in `.env`.

## Tests

```bash
php artisan test
```

## GitHub and hosting

This repository intentionally excludes `.env`, `database/database.sqlite`, `vendor`, and `node_modules`. After adding your GitHub remote:

```bash
git add .
git commit -m "Build Kumburus hotel booking system"
git branch -M main
git remote add origin https://github.com/YOUR-ACCOUNT/kumburus.git
git push -u origin main
```

For hosting, configure the same environment variables on a Laravel-compatible host, run the migrations and seeders, point the web root at `public/`, and set `MAIL_MAILER` to your provider's SMTP/API transport.
