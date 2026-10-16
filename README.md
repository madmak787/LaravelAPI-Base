# LaravelAPI-Base

A starter kit for API-only Laravel 12 backends by [Mohd Amir Khan](https://khanamir.me). It gives you separate user and admin authentication with Laravel Sanctum, role-scoped token abilities, a settings store and a sample CRUD resource, so a new project starts with the boring parts done.

It pairs with [NextJS-Base](https://github.com/madmak787/NextJS-Base) for the front end.

## What's included

- **Two guards, one API:** `POST /api/user/login` and `POST /api/admin/login` issue Sanctum tokens with `user:*` or `admin:*` abilities, and route groups check them with the `abilities` middleware
- **Admin profile:** view and update the profile, change the password, log out
- **Settings:** admins read and upsert key/value settings; `GET /api/settings` exposes the public ones to the front end
- **Sample resource:** `apiResource('data')` shows the controller, model and migration pattern to copy
- Laravel 12 conventions: configuration in `bootstrap/app.php`, no `Kernel.php`

## Routes

| Method | Path | Access |
| --- | --- | --- |
| GET | `/api/settings` | Public |
| POST | `/api/user/login` | Public |
| GET | `/api/user/me` | User token |
| POST | `/api/user/logout` | User token |
| POST | `/api/admin/login` | Public |
| GET, POST | `/api/admin/profile` | Admin token |
| POST | `/api/admin/change-password` | Admin token |
| GET, POST | `/api/admin/settings` | Admin token |
| CRUD | `/api/admin/data` | Admin token |

## Getting started

Requires PHP 8.2+ and Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API is then available at `http://localhost:8000/api`.

## Deploy

`.github/workflows/deploy-laravel-api.yml` deploys the `Development`, `Staging`, `UAT` and `Production` branches over FTP and runs the post-deploy Artisan commands over SSH. Set the server paths and secrets for your own project before using it.
