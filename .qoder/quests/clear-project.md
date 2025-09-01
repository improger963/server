# Clear Project and Set Up Clean Laravel Project

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
   - Users table with role column
   - Sites table
   - Campaigns table
   - Referrals table
   - Cache and jobs tables

4. **Container Architecture**:
   - Dockerfile for PHP 8.4 with Laravel Octane and RoadRunner
   - Docker Compose with PostgreSQL 16 and Redis 7

## Clear Project Plan

### 1. Remove Custom Application Code

Remove all custom controllers, models, and related files:

- Delete all files in `app/Http/Controllers/` except `Controller.php`
- Delete all files in `app/Models/` except the base User model
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
- Keep essential Laravel packages:
  - laravel/framework
  - laravel/octane
  - spiral/roadrunner
  - tymon/jwt-auth
  - predis/predis
  - guzzlehttp/guzzle

## Clean Laravel Project Structure

After cleaning, the project should have the following minimal structure:

```
.
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   └── Controller.php
│   │   └── Middleware
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

3. Clean routes:
   - Update `routes/api.php` to remove all custom routes
   - Update `routes/web.php` to have minimal content
   - Update `routes/console.php` to have minimal content

### Step 3: Remove Custom Migrations

Remove custom migration files:
```bash
rm database/migrations/2025_09_01_133845_add_role_to_users_table.php
rm database/migrations/2025_09_01_133911_create_sites_table.php
rm database/migrations/2025_09_01_133932_create_campaigns_table.php
rm database/migrations/2025_09_01_133955_create_referrals_table.php
```

### Step 4: Reset Configuration Files

Reset configuration files to Laravel defaults:
- `config/app.php`
- `config/auth.php`
- `config/database.php`

### Step 5: Clean Composer Dependencies

Update `composer.json` to remove unnecessary packages while keeping essential ones.

### Step 6: Clean Tests

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
   - PHP 8.4 with necessary extensions
   - Laravel Octane with RoadRunner
   - Node.js and NPM for frontend assets

2. **Docker Compose**:
   - PostgreSQL 16 database service
   - Redis 7 caching service
   - Application service with proper environment variables

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

3. Default Laravel welcome page is accessible

4. All tests pass:
   ```bash
   docker-compose exec smartlink-app php artisan test
   ```

## Benefits of Clean Laravel Project

1. **Fresh Start**: Eliminates legacy code and unnecessary complexity
2. **Standard Structure**: Follows Laravel conventions and best practices
3. **Improved Maintainability**: Easier to understand and modify
4. **Better Performance**: Removes unused code that could impact performance
5. **Security**: Eliminates potential vulnerabilities in custom code
6. **Scalability**: Clean base for building new features

## Conclusion

This cleaning process will result in a minimal, clean Laravel project that maintains the high-performance container architecture with Docker, PostgreSQL, and Redis. The project will be ready for new feature development with a solid foundation.

