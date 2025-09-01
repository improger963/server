# SmartLink Advertising Platform - Project Documentation

## 1. Overview

SmartLink is a comprehensive digital advertising platform built with Laravel 12 that enables users to create, manage, and monetize online advertising campaigns. The system provides a complete ecosystem for advertisers to run campaigns and publishers to earn revenue by displaying ads on their websites.

### Core Features

- **User Management**: Complete user authentication and profile management with balance tracking
- **Site Management**: Publishers can register and manage websites for ad placements
- **Ad Slot System**: Flexible ad slot configuration with various ad formats (banners, links, contextual ads)
- **Campaign Management**: Advertisers can create campaigns with budget allocation and scheduling
- **Creative Management**: Support for multiple ad creative types (images, text links, contextual ads)
- **Financial System**: Integrated deposit/withdrawal functionality with secure transaction processing
- **Real-time Analytics**: Comprehensive dashboard with performance metrics and reporting
- **Notification System**: Real-time notifications for important events and updates
- **Chat System**: Real-time messaging between users with WebSocket integration
- **Referral Program**: Built-in referral system with earnings tracking

## 2. Technology Stack & Dependencies

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.4
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis 7
- **Authentication**: Laravel Sanctum
- **Real-time**: Laravel Reverb (WebSocket)

### Development & Deployment
- **Containerization**: Docker
- **Orchestration**: Docker Compose
- **Package Management**: Composer
- **Testing**: PHPUnit 11

### Frontend (Assets)
- **Build Tool**: Vite 7.0.4
- **Styling**: TailwindCSS 4.0.0

## 3. Architecture

SmartLink follows a traditional MVC architecture pattern with additional service and middleware layers for business logic separation and access control.

### System Components

#### Models
- **User**: Core user entity with balance and referral management
- **Site**: Advertising sites owned by publishers
- **AdSlot**: Ad placement positions within sites with pricing configuration
- **Campaign**: Advertising campaigns with budget tracking and scheduling
- **Creative**: Ad content assets associated with campaigns
- **TransactionLog**: Financial transaction history
- **Withdrawal**: Withdrawal requests and processing
- **AnalyticsEvent**: User activity and performance tracking

#### Controllers
- **AuthController**: User authentication and registration
- **SiteController**: Site management operations
- **AdSlotController**: Ad slot management and public ad serving
- **CampaignController**: Campaign management with budget operations
- **CreativeController**: Creative asset management
- **FinancialController**: Deposit and withdrawal operations
- **StatsController**: Analytics dashboard and reporting
- **NotificationController**: User notifications management
- **ChatController**: Real-time messaging system

#### Services
- **CampaignService**: Budget allocation, campaign activation, and lifecycle management
- **FinancialService**: Deposit, withdrawal, and transaction processing
- **AdSlotService**: Ad serving logic and campaign association
- **PayeerService**: Payment gateway integration
- **ReferralService**: Referral program logic and earnings calculation
- **ValidationService**: Input validation for all entities

#### Middleware
- **OwnershipMiddleware**: Abstract base class for ownership validation
- **SiteOwnershipMiddleware**: Validates site ownership
- **CampaignOwnershipMiddleware**: Validates campaign ownership
- **AdSlotOwnershipMiddleware**: Validates ad slot ownership
- **CreativeOwnershipMiddleware**: Validates creative ownership

## 4. API Endpoints Reference

### Authentication
All API endpoints require authentication via Laravel Sanctum. Include the `Authorization: Bearer <token>` header with your requests.

#### User Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `POST /api/forgot-password` - Request password reset
- `POST /api/reset-password` - Reset password

#### Resource Endpoints
- **Sites**: `/api/sites`
- **Ad Slots**: `/api/sites/{site}/ad-slots`
- **Campaigns**: `/api/campaigns`
- **Creatives**: `/api/campaigns/{campaign}/creatives`
- **Financial**: `/api/deposit`, `/api/withdraw`
- **Statistics**: `/api/stats/dashboard`
- **Notifications**: `/api/notifications`
- **Chat**: `/api/chat/messages`
- **News**: `/api/news`

#### Special Operations
- **Campaign Budget Allocation**: `POST /api/campaigns/{campaign}/allocate-budget`
- **Campaign Activation**: `POST /api/campaigns/{campaign}/activate`
- **Campaign Deactivation**: `POST /api/campaigns/{campaign}/deactivate`

### Authentication Requirements
- All endpoints except Payeer webhook and public ad requests require authentication
- Laravel Sanctum tokens are used for API authentication
- Ownership middleware validates access to user-specific resources

### API Endpoints Details

#### Register
**Endpoint:** `POST /api/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "balance": "0.00",
    "frozen_balance": "0.00",
    "created_at": "2025-09-01T10:00:00.000000Z",
    "updated_at": "2025-09-01T10:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz"
}
```

#### Login
**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "balance": "0.00",
    "frozen_balance": "0.00",
    "created_at": "2025-09-01T10:00:00.000000Z",
    "updated_at": "2025-09-01T10:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz"
}
```

#### Logout
**Endpoint:** `POST /api/logout`

**Headers:**
- `Authorization: Bearer <token>`

**Response:**
- 204 No Content (Success)

#### Deposit Funds
**Endpoint:** `POST /api/deposit`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "amount": 100.00
}
```

**Response:**
```json
{
  "message": "Deposit successful",
  "balance": 150.00,
  "amount": 100.00
}
```

#### Withdraw Funds
**Endpoint:** `POST /api/withdraw`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "amount": 50.00
}
```

**Response:**
```json
{
  "message": "Withdrawal request created successfully",
  "balance": 50.00,
  "frozen_balance": 50.00,
  "amount": 50.00
}
```

#### Get Dashboard Statistics
**Endpoint:** `GET /api/stats/dashboard`

**Headers:**
- `Authorization: Bearer <token>`

**Query Parameters:**
- `period` (optional): Filter by time period. Options: `today`, `week`, `month`, `year`. Default: `month`.

**Response:**
```json
{
  "revenue": 250.00,
  "spend": 1000.00,
  "impressions": 5000,
  "clicks": 250,
  "ctr": 5.00
}
```

## 5. Data Models & ORM Mapping

### User Model
```php
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'frozen_balance',
        'referrer_id',
        'referral_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
        'frozen_balance' => 'decimal:2',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function creatives()
    {
        return $this->hasMany(Creative::class);
    }
}
```

### Campaign Model
```php
class Campaign extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'budget',
        'spent',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creatives()
    {
        return $this->hasMany(Creative::class);
    }

    public function adSlots()
    {
        return $this->belongsToMany(AdSlot::class);
    }
}
```

### AdSlot Model
```php
class AdSlot extends Model
{
    protected $fillable = [
        'site_id',
        'name',
        'type',
        'dimensions',
        'price_per_click',
        'price_per_impression',
        'is_active',
    ];

    protected $casts = [
        'price_per_click' => 'decimal:4',
        'price_per_impression' => 'decimal:4',
        'is_active' => 'boolean',
        'dimensions' => 'array',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class);
    }
}
```

### Creative Model
```php
class Creative extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'type',
        'content',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'content' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
```

### Site Model
```php
class Site extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adSlots()
    {
        return $this->hasMany(AdSlot::class);
    }
}
```

## 6. Business Logic Layer

### Financial Management System

The financial system is designed to ensure secure handling of user funds:

1. **User Balance Management**
   - Users maintain account balances for funding campaigns
   - Balance operations include add, deduct, freeze, and unfreeze functions
   - Frozen balances are used for withdrawal processing

2. **Campaign Budget Allocation**
   - Users allocate funds from their balance to campaigns
   - Budget allocation uses database transactions to ensure atomicity
   - Transaction logs record all budget movements

3. **Campaign Spending**
   - Budgets are consumed as campaigns run and ads are displayed
   - Automatic campaign deactivation when budgets are exhausted
   - Real-time budget tracking and validation

4. **Budget Return**
   - Unused campaign budgets are returned to user balances when campaigns end
   - Automatic processing of budget returns for expired campaigns

5. **Withdrawal Processing**
   - Users can request withdrawals through a secure freeze-and-process workflow
   - Withdrawal requests freeze funds in a separate frozen balance
   - Admin approval and processing workflows

### Access Control System

The platform implements robust ownership-based access control:

1. **Ownership Validation**
   - All entities implement ownership validation through middleware
   - Users can only access their own sites, campaigns, ad slots, and creatives
   - Abstract ownership middleware provides consistent validation patterns

2. **Resource Scoping**
   - API endpoints are scoped to user-owned resources
   - Database queries are automatically scoped to authenticated users
   - Direct access to other users' resources is prevented

### Campaign Lifecycle Management

Campaigns follow a well-defined lifecycle with automated management:

1. **Creation Phase**
   - Campaigns are created with name, description, budget, and scheduling
   - Initial validation of budget and date parameters
   - Association with user account

2. **Budget Allocation Phase**
   - Users allocate funds from their balance to campaigns
   - Budget validation and transaction processing
   - Budget tracking initialization

3. **Activation Phase**
   - Campaigns can be activated when they have sufficient budget and are within date range
   - Activation validation and status update
   - Association with ad slots for serving

4. **Running Phase**
   - Active campaigns display creatives in associated ad slots
   - Real-time budget consumption tracking
   - Performance analytics collection

5. **Deactivation Phase**
   - Campaigns automatically deactivate when budgets are exhausted or dates expire
   - Automated deactivation through scheduled jobs
   - Status update and notification

6. **Completion Phase**
   - Completed campaigns return unused budgets to user balances
   - Final analytics processing
   - Archive and reporting

### Ad Serving System

The ad serving system efficiently matches campaigns with ad slots:

1. **Ad Slot Configuration**
   - Publishers create ad slots with pricing information
   - Ad slot types and dimensions configuration
   - Activation status management

2. **Campaign Association**
   - Advertisers associate campaigns with relevant ad slots
   - Validation of campaign eligibility for ad slots
   - Many-to-many relationship management

3. **Ad Selection**
   - The system selects appropriate creatives from active campaigns
   - Real-time validation of campaign status and budget
   - Creative type matching with ad slot requirements

4. **Real-time Serving**
   - Ads are served in real-time to website visitors
   - Performance tracking and analytics collection
   - Revenue distribution to publishers

## 7. Middleware & Interceptors

### Ownership Middleware

Abstract base class for ownership validation:
```php
abstract class OwnershipMiddleware
{
    public function handle($request, Closure $next)
    {
        $entity = $this->getEntity($request);
        if ($entity->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return $next($request);
    }
    
    abstract protected function getEntity($request);
}
```

### Entity-Specific Middleware

- **SiteOwnershipMiddleware**: Validates site ownership
- **CampaignOwnershipMiddleware**: Validates campaign ownership
- **AdSlotOwnershipMiddleware**: Validates ad slot ownership
- **CreativeOwnershipMiddleware**: Validates creative ownership

## 8. Testing

### Unit Tests

Unit tests cover individual components and business logic:

1. **Model Tests**
   - User balance operations
   - Campaign budget management
   - Ad slot functionality
   - Creative validation

2. **Service Tests**
   - CampaignService budget allocation
   - FinancialService withdrawal processing
   - AdSlotService ad serving logic
   - ValidationService input validation

3. **Middleware Tests**
   - Ownership validation for all entity types
   - Access control enforcement
   - Error response handling

### Feature Tests

Feature tests cover API endpoints and user workflows:

1. **Authentication Tests**
   - User registration and login
   - Password reset functionality
   - Token-based authentication

2. **Resource Management Tests**
   - CRUD operations for sites, ad slots, campaigns, and creatives
   - Ownership validation for all operations
   - Data validation and error handling

3. **Financial Tests**
   - Deposit and withdrawal workflows
   - Budget allocation and spending
   - Transaction logging

4. **Real-time Feature Tests**
   - Chat messaging functionality
   - Notification delivery
   - WebSocket connectivity

### Test Commands

- `docker-compose exec smartlink-app php artisan test` - Run all tests
- `docker-compose exec smartlink-app php artisan test --filter=Feature` - Run feature tests
- `docker-compose exec smartlink-app php artisan test --filter=Unit` - Run unit tests


















































































































