# Clean Laravel Project Setup

## Overview

This document outlines the process of clearing the existing Laravel project and setting up a clean, minimal Laravel installation. The current project contains custom controllers, models, and migrations that need to be removed to create a fresh start.

## Current Project Analysis

The existing project is a Laravel-based backend with the following components:

1. **Custom Controllers**:
   - AuthController.php
   - CampaignController.php
   - ReferralController.php
   - SiteController.php

2. **Custom Models**:
   - Campaign.php
   - Referral.php
   - Site.php
   - User.php (with role column)

3. **Database Migrations**:
   - Users table with role column (`2025_09_01_133845_add_role_to_users_table.php`)
   - Sites table (`2025_09_01_133911_create_sites_table.php`)
   - Campaigns table (`2025_09_01_133932_create_campaigns_table.php`)
   - Referrals table (`2025_09_01_133955_create_referrals_table.php`)
   - Cache and jobs tables (Laravel default migrations)

4. **Container Architecture**:
   - Dockerfile for PHP 8.4 with necessary extensions (pdo, pdo_pgsql, mbstring, exif, pcntl, bcmath, gd)
   - Docker Compose with PostgreSQL 16 and Redis 7
   - Laravel Octane with RoadRunner

## Clear Project Plan

### 1. Remove Custom Application Code

Remove all custom controllers, models, and related files:

- Delete all files in `app/Http/Controllers/` except `Controller.php`
- Delete all files in `app/Models/` except the base User model
- Delete all custom middleware files in `app/Http/Middleware/` (JwtMiddleware.php, RoleMiddleware.php, SmartGuardMiddleware.php)
- Remove custom routes from `routes/api.php`
- Clean up `routes/web.php` and `routes/console.php`

### 2. Reset Database Migrations

- Remove all custom migration files:
  - `2025_09_01_133845_add_role_to_users_table.php`
  - `2025_09_01_133911_create_sites_table.php`
  - `2025_09_01_133932_create_campaigns_table.php`
  - `2025_09_01_133955_create_referrals_table.php`
- Keep only the base Laravel migrations:
  - `0001_01_01_000000_create_users_table.php`
  - `0001_01_01_000001_create_cache_table.php`
  - `0001_01_01_000002_create_jobs_table.php`

### 3. Clean Configuration Files

- Reset `config/app.php` to default values
- Clean up `config/auth.php` to remove custom guards
- Reset other configuration files to Laravel defaults

### 4. Update Composer Dependencies

- Remove unnecessary packages from `composer.json`
- Keep essential Laravel packages based on current configuration:
  - laravel/framework (^12.0)
  - laravel/octane (^2.12)
  - spiral/roadrunner (^2025.1)
  - tymon/jwt-auth (^2.2)
  - predis/predis (^3.2)
  - guzzlehttp/guzzle (^7.10)
  - laravel/tinker (^2.10.1)

## Clean Laravel Project Structure

After cleaning, the project should have the following minimal structure:

```
.
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   └── Controller.php
│   │   └── Middleware
│   │       └── (Laravel default middleware configuration in bootstrap/app.php)
│   ├── Models
│   │   └── User.php
│   └── Providers
├── bootstrap
├── config
├── database
│   ├── factories
│   ├── migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   └── seeders
├── public
├── resources
├── routes
│   ├── api.php
│   ├── console.php
│   └── web.php
├── tests
├── Dockerfile
├── docker-compose.yml
└── composer.json
```

## Implementation Steps

### Step 1: Backup Current Project

Before making any changes, create a backup of the current project:

```bash
# Create a backup branch
git checkout -b backup/current-project

# Commit all changes
git add .
git commit -m "Backup of current project before cleaning"

# Return to main branch
git checkout main
```

### Step 2: Remove Custom Application Files

1. Remove custom controllers:
   ```bash
   rm app/Http/Controllers/AuthController.php
   rm app/Http/Controllers/CampaignController.php
   rm app/Http/Controllers/ReferralController.php
   rm app/Http/Controllers/SiteController.php
   ```

2. Remove custom models:
   ```bash
   rm app/Models/Campaign.php
   rm app/Models/Referral.php
   rm app/Models/Site.php
   ```

3. Remove custom middleware:
   ```bash
   rm app/Http/Middleware/JwtMiddleware.php
   rm app/Http/Middleware/RoleMiddleware.php
   rm app/Http/Middleware/SmartGuardMiddleware.php
   ```

4. Update middleware configuration in `bootstrap/app.php` to remove references to custom middleware aliases

3. Clean routes:
   - Update `routes/api.php` to remove all custom routes (currently contains routes for auth, campaigns, sites, and referrals)
   - Update `routes/web.php` to have minimal content (currently contains default welcome route)
   - Update `routes/console.php` to have minimal content (currently contains default inspire command)

### Step 3: Remove Custom Migrations

Remove custom migration files:
```bash
rm database/migrations/2025_09_01_133845_add_role_to_users_table.php
rm database/migrations/2025_09_01_133911_create_sites_table.php
rm database/migrations/2025_09_01_133932_create_campaigns_table.php
rm database/migrations/2025_09_01_133955_create_referrals_table.php
```

### Step 4: Clean Database Seeders and Factories

Reset database seeders to Laravel defaults:
- Update `database/seeders/DatabaseSeeder.php` to use default Laravel seeding logic (comment out custom user creation)

Clean database factories:
- Keep `database/factories/UserFactory.php` as it is a Laravel default
- Remove any custom factory files (none currently exist in this project)

### Step 5: Reset Configuration Files

Reset configuration files to Laravel defaults:
- `config/app.php`
- `config/auth.php`
- `config/database.php`

Update `bootstrap/app.php` to remove middleware aliases and configurations for custom middleware

### Step 6: Clean Composer Dependencies

Update `composer.json` to remove unnecessary packages while keeping essential ones.

### Step 7: Clean Tests

Remove custom test files:
```bash
rm tests/Feature/AuthenticationTest.php
rm tests/Feature/CampaignTest.php
rm tests/Feature/ReferralTest.php
rm tests/Feature/SiteTest.php
rm tests/Unit/CampaignTest.php
rm tests/Unit/ReferralTest.php
rm tests/Unit/SiteTest.php
rm tests/Unit/UserTest.php
```

## Container Architecture Preservation

The Docker configuration should be preserved as it provides a solid foundation:

1. **Dockerfile**:
   - PHP 8.4-fpm-alpine base image
   - System dependencies: git, curl, libpng-dev, oniguruma-dev, libxml2-dev, zip, unzip, nodejs, npm, postgresql-dev, linux-headers
   - PHP extensions: pdo, pdo_pgsql, mbstring, exif, pcntl, bcmath, gd
   - Composer installation
   - Working directory: /var/www/html
   - File permissions set to 777 for development
   - Exposes port 9000

2. **Docker Compose**:
   - PostgreSQL 16-alpine database service with environment configuration
   - Redis 7-alpine caching service
   - Application service (smartlink-app) with proper environment variables for database and Redis connections
   - Volume mapping for development
   - Service dependencies: app depends on database and Redis
   - Port mappings: 9000 for app, 5432 for database, 6379 for Redis

## Post-Cleanup Verification

After cleaning the project, verify the following:

1. Application starts correctly with Docker:
   ```bash
   docker-compose up -d
   ```

2. Database migrations run successfully:
   ```bash
   docker-compose exec smartlink-app php artisan migrate
   ```

3. Default Laravel welcome page is accessible at the configured port

4. All tests pass:
   ```bash
   docker-compose exec smartlink-app php artisan test
   ```

5. Environment variables are properly configured for database and Redis connections

## Benefits of Clean Laravel Project

1. **Fresh Start**: Eliminates legacy code and unnecessary complexity
2. **Standard Structure**: Follows Laravel conventions and best practices
3. **Improved Maintainability**: Easier to understand and modify
4. **Better Performance**: Removes unused code that could impact performance
5. **Security**: Eliminates potential vulnerabilities in custom code
6. **Scalability**: Clean base for building new features

## Conclusion

This cleaning process will result in a minimal, clean Laravel project that maintains the high-performance container architecture with Docker, PostgreSQL, and Redis. The project will be ready for new feature development with a solid foundation.