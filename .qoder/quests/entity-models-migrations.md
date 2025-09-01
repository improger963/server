# Entity Models and Migrations Design

## 1. Overview

This document outlines the design for entity models and database migrations required for the SmartLink advertising platform. The system will support users (both webmasters and advertisers), sites, ad slots, campaigns, and creatives with financial functionality for USDT transactions.

## 2. Technology Stack & Dependencies

- PHP 8.2+ with Laravel 11 Framework
- Laravel Sanctum for API Authentication
- PostgreSQL Database (via Docker)
- Payeer API for USDT Payment Processing

## 3. Data Models & ORM Mapping

### 3.1 User Model

The User model extends the base Laravel User model with additional fields for balance tracking:

**Fields:**
- id (bigint, primary key, auto-increment)
- name (string, 255 characters)
- email (string, 255 characters, unique)
- email_verified_at (timestamp, nullable)
- password (string, 255 characters)
- balance (decimal, 15 digits with 2 decimal places, default 0.00)
- remember_token (string, 100 characters, nullable)
- created_at (timestamp)
- updated_at (timestamp)

**Relationships:**
- hasMany Site (user_id)
- hasMany Campaign (user_id)

### 3.2 Site Model

Represents websites owned by webmasters for ad placement:

**Fields:**
- id (bigint, primary key, auto-increment)
- user_id (bigint, foreign key to users.id)
- url (string, 2048 characters)
- status (enum: active, inactive, suspended)
- created_at (timestamp)
- updated_at (timestamp)

**Relationships:**
- belongsTo User (user_id)
- hasMany AdSlot (site_id)

### 3.3 AdSlot Model

Represents specific ad placements on webmaster sites:

**Fields:**
- id (bigint, primary key, auto-increment)
- site_id (bigint, foreign key to sites.id)
- format (enum: 468x60, 728x90, 200x300, text_link)
- created_at (timestamp)
- updated_at (timestamp)

**Relationships:**
- belongsTo Site (site_id)

### 3.4 Campaign Model

Represents advertising campaigns created by advertisers:

**Fields:**
- id (bigint, primary key, auto-increment)
- user_id (bigint, foreign key to users.id)
- name (string, 255 characters)
- status (enum: active, paused, completed, deleted)
- created_at (timestamp)
- updated_at (timestamp)

**Relationships:**
- belongsTo User (user_id)
- hasMany Creative (campaign_id)

### 3.5 Creative Model

Represents the actual ad content (banners or text links) associated with campaigns:

**Fields:**
- id (bigint, primary key, auto-increment)
- campaign_id (bigint, foreign key to campaigns.id)
- ad_slot_format (enum: 468x60, 728x90, 200x300, text_link)
- type (enum: banner, text)
- content (jsonb for URL of image or text content)
- created_at (timestamp)
- updated_at (timestamp)

**Relationships:**
- belongsTo Campaign (campaign_id)

## 4. Database Migrations

### 4.1 Users Table Extension

Extending the existing users table with a balance field for USDT tracking:

```php
Schema::table('users', function (Blueprint $table) {
    $table->decimal('balance', 15, 2)->default(0.00)->after('password');
});
```

### 4.2 Sites Table Migration

Creating a new table to store webmaster sites:

```php
Schema::create('sites', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('url', 2048);
    $table->enum('status', ['active', 'inactive', 'suspended'])->default('inactive');
    $table->timestamps();
});
```

### 4.3 AdSlots Table Migration

Creating a new table for ad placements on sites:

```php
Schema::create('ad_slots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('site_id')->constrained()->onDelete('cascade');
    $table->enum('format', ['468x60', '728x90', '200x300', 'text_link']);
    $table->timestamps();
});
```

### 4.4 Campaigns Table Migration

Creating a new table for advertiser campaigns:

```php
Schema::create('campaigns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->enum('status', ['active', 'paused', 'completed', 'deleted'])->default('active');
    $table->timestamps();
});
```

### 4.5 Creatives Table Migration

Creating a new table for ad creatives:

```php
Schema::create('creatives', function (Blueprint $table) {
    $table->id();
    $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
    $table->enum('ad_slot_format', ['468x60', '728x90', '200x300', 'text_link']);
    $table->enum('type', ['banner', 'text']);
    $table->jsonb('content');
    $table->timestamps();
});
```

## 5. API Endpoints Reference

### 5.1 Authentication API

**POST /api/register**
- Request: name, email, password
- Response: user object with Sanctum token

**POST /api/login**
- Request: email, password
- Response: user object with Sanctum token

**POST /api/logout**
- Request: none (requires authentication)
- Response: success message

### 5.2 Site Management API

All endpoints require authentication and validate ownership.

**GET /api/sites**
- Response: list of sites owned by authenticated user

**POST /api/sites**
- Request: url, status
- Response: created site object

**GET /api/sites/{id}**
- Response: site object if owned by authenticated user

**PUT /api/sites/{id}**
- Request: url, status
- Response: updated site object if owned by authenticated user

**DELETE /api/sites/{id}**
- Response: success message if owned by authenticated user

### 5.3 Campaign Management API

All endpoints require authentication and validate ownership.

**GET /api/campaigns**
- Response: list of campaigns owned by authenticated user

**POST /api/campaigns**
- Request: name, status
- Response: created campaign object

**GET /api/campaigns/{id}**
- Response: campaign object if owned by authenticated user

**PUT /api/campaigns/{id}**
- Request: name, status
- Response: updated campaign object if owned by authenticated user

**DELETE /api/campaigns/{id}**
- Response: success message if owned by authenticated user

### 5.4 Financial API

All endpoints require authentication.

**POST /api/deposit**
- Request: amount
- Response: USDT wallet address for deposit

**POST /api/withdraw**
- Request: amount, wallet_address
- Response: withdrawal request confirmation

## 6. Business Logic Layer

### 6.1 Ownership-based Access Control

All resources are accessed based on user ownership rather than roles. Each resource (Site, Campaign) has a user_id field that links to the owner.

### 6.2 Financial Operations

Integration with Payeer API for USDT transactions:
- Deposit: Generate unique wallet address for each deposit request
- Withdrawal: Validate balance and create withdrawal request in queue

## 7. Middleware & Interceptors

### 7.1 Authentication Middleware

Using Laravel Sanctum for API token authentication.

### 7.2 Ownership Middleware

Custom middleware to verify that the authenticated user owns the requested resource.

## 8. Testing

### 8.1 Unit Tests

- User model tests (balance operations)
- Site model tests (validation, relationships)
- Campaign model tests (validation, relationships)
- Creative model tests (validation, relationships)

### 8.2 Feature Tests

- Authentication API endpoints
- Site management endpoints
- Campaign management endpoints
- Financial endpoints

## 9. Security Considerations

- Laravel Sanctum for API authentication
- Ownership validation for all resource access
- Input validation for all API endpoints
- SQL injection prevention through Eloquent ORM
- XSS prevention through Laravel's built-in escaping