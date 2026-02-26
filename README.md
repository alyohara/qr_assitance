# QR Attendance System (Laravel)

A production-oriented attendance platform for virtual classes, built with Laravel.

This project provides dynamic rotating QR tokens plus a professor PIN to prevent students from reusing static QR screenshots. Teachers can create subjects, open class sessions, control QR rotation time, and review/export attendance data.

## Table of Contents

- [Key Features](#key-features)
- [How It Works](#how-it-works)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Requirements](#requirements)
- [Local Development Setup](#local-development-setup)
- [Environment Variables](#environment-variables)
- [Production Deployment (Nginx)](#production-deployment-nginx)
- [Usage Flow](#usage-flow)
- [Security Notes](#security-notes)
- [Testing](#testing)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [License](#license)

## Key Features

- Dynamic QR code generation with configurable rotation window (for example every 30 seconds)
- Per-session professor PIN (6 digits) required for attendance submission
- Subject and class-session management panel
- Public student attendance form (scan QR + enter PIN)
- Duplicate attendance protection (one attendance per student per session)
- CSV export by class session
- CSV export by subject
- Date-based reporting dashboard with filters

## How It Works

1. Teacher creates a class session linked to a subject.
2. System generates rotating QR payloads signed per time window.
3. Student scans the QR code and submits identity + professor PIN.
4. Backend validates:
   - Session is active
   - QR window signature is valid and not stale
   - PIN matches current session
5. Attendance is stored and shown in the live session panel.

## Tech Stack

- Backend: Laravel 12, PHP 8.2+
- Frontend: Blade + Tailwind CSS + Alpine.js
- QR Rendering: JavaScript `qrcode` package
- Database: MySQL / MariaDB (SQLite supported for local testing)
- Web Server: Nginx + PHP-FPM

## Project Structure

- `app/Models`: Domain entities (`Subject`, `ClassSession`, `Student`, `Attendance`)
- `app/Http/Controllers`: Session logic, attendance intake, exports, reports
- `database/migrations`: Database schema
- `resources/views`: Teacher dashboard, management pages, student scan page
- `routes/web.php`: HTTP routes

## Requirements

- PHP 8.2 or newer
- Composer 2+
- Node.js 20+ and npm
- MySQL 8+ / MariaDB 10.5+
- Nginx + PHP-FPM for production

## Local Development Setup

### 1) Clone and install dependencies

```bash
git clone https://github.com/<your-user>/<your-repo>.git
cd <your-repo>
composer install
npm install
```

### 2) Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` database values according to your local setup.

### 3) Run migrations and build assets

```bash
php artisan migrate
npm run build
```

### 4) Start development environment

```bash
composer run dev
```

This starts Laravel app server + queue listener + logs + Vite dev process.

## Environment Variables

Minimum required values for production:

```env
APP_NAME="QR Attendance"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://attendance.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_assistance
DB_USERNAME=qr_user
DB_PASSWORD=your_secure_password
```

## Production Deployment (Nginx)

Recommended approach:

1. Deploy source code with GitHub (`git clone` / `git pull`).
2. Install dependencies:
   - `composer install --no-dev --optimize-autoloader`
   - `npm ci && npm run build`
3. Set proper permissions on `storage` and `bootstrap/cache`.
4. Run database migration:
   - `php artisan migrate --force`
5. Cache configuration:
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
6. Configure Nginx root to `public/` and route PHP via PHP-FPM.
7. Enable HTTPS with Let's Encrypt.

## Usage Flow

### Teacher

- Create subjects
- Create class sessions
- Configure:
  - Session time window
  - QR rotation seconds
  - Professor PIN
- Open live session view
- Share rotating QR and PIN with students
- Export attendance to CSV

### Student

- Scan QR from teacher screen
- Fill student identity fields
- Enter professor PIN
- Submit attendance

## Security Notes

- Rotating QR windows reduce replay from shared screenshots.
- QR payload is signature-validated server-side.
- Attendance is accepted only for active sessions.
- Session PIN adds second-factor classroom verification.
- Duplicate records are blocked per student/session pair.

> Important: for internet-exposed deployments, always use HTTPS, strong credentials, firewall rules, and regular backups.

## Testing

Run test suite:

```bash
php artisan test
```

## Roadmap

- CSV export for report summaries
- Optional student authentication (institution SSO)
- Real-time attendance feed updates (WebSockets)
- Multi-teacher admin role with centralized analytics

## Contributing

Contributions are welcome.

1. Fork the repository
2. Create a feature branch
3. Open a pull request with clear context and test notes

Please keep changes focused and production-safe.

## License

This project is released under the MIT License.<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
