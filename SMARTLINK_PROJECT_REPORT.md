# SmartLink Server Project Report

## 1. Project Overview

SmartLink is a Laravel-based backend server application designed for managing digital advertising campaigns. The system provides a comprehensive platform for users to create and manage advertising sites, ad slots, campaigns, and creatives with integrated financial management.

### Core Features
- User account management with balance tracking
- Site management for advertising placements
- Ad slot creation and configuration
- Campaign creation with budget management
- Creative asset management
- Financial operations (deposit/withdraw)
- Ownership-based access control

## 2. Technology Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.4
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis 7
- **Authentication**: Laravel Sanctum

### Development & Deployment
- **Containerization**: Docker
- **Orchestration**: Docker Compose
- **Package Management**: Composer
- **Testing**: PHPUnit 11

### Frontend (Assets)
- **Build Tool**: Vite 7.0.4
- **Styling**: TailwindCSS 4.0.0

## 3. System Architecture

The application follows a traditional MVC architecture pattern with additional service and middleware layers for business logic separation and access control.

### Key Components

#### Models
- **User**: Core user entity with balance management
- **Site**: Advertising sites owned by users
- **AdSlot**: Ad placement positions within sites
- **Campaign**: Advertising campaigns with budget tracking
- **Creative**: Ad content assets associated with campaigns

#### Controllers
- **SiteController**: Site management operations
- **AdSlotController**: Ad slot management operations
- **CampaignController**: Campaign management with budget operations
- **CreativeController**: Creative asset management
- **FinancialController**: Deposit and withdrawal operations

#### Services
- **CampaignService**: Budget allocation and management logic
- **AdSlotService**: Ad display and campaign association logic
- **ValidationService**: Input validation for all entities

#### Middleware
- **OwnershipMiddleware**: Abstract base class for ownership validation
- **SiteOwnershipMiddleware**: Validates site ownership
- **CampaignOwnershipMiddleware**: Validates campaign ownership
- **AdSlotOwnershipMiddleware**: Validates ad slot ownership
- **CreativeOwnershipMiddleware**: Validates creative ownership

## 4. Database Design

### Key Tables
- **users**: User accounts with balance tracking
- **sites**: Advertising sites with URL and status
- **ad_slots**: Ad placements with pricing information
- **campaigns**: Advertising campaigns with budget and scheduling
- **creatives**: Ad content with type and data
- **ad_slot_campaign**: Junction table for ad slot-campaign relationships

### Relationships
- Users have many Sites, Campaigns, and Creatives (1:N)
- Sites belong to Users and have many AdSlots (1:N)
- AdSlots belong to Sites and have many Campaigns (N:M)
- Campaigns belong to Users and have many Creatives (1:N)
- Creatives belong to Campaigns (N:1)

## 5. Business Logic

### Financial Management
- Users maintain account balances for campaign funding
- Campaign budgets are allocated from user balances
- Budgets are deducted as campaigns run
- Automatic campaign deactivation when budgets are exhausted
- Budget release back to user balance when campaigns end

### Access Control
- All entities implement ownership-based access control
- Middleware validates ownership for all CRUD operations
- Users can only access their own sites, campaigns, ad slots, and creatives

### Campaign Lifecycle
- Campaigns have start/end dates and activation status
- Only active campaigns within date range can run
- Budget management ensures campaigns don't overspend
- Creatives are only displayed for active campaigns

## 6. API Structure

### Authentication
- All API endpoints require authentication via Laravel Sanctum

### Resource Endpoints
- **Sites**: `/api/sites`
- **Ad Slots**: `/api/sites/{site}/ad-slots`
- **Campaigns**: `/api/campaigns`
- **Creatives**: `/api/campaigns/{campaign}/creatives`
- **Financial**: `/api/deposit`, `/api/withdraw`

### Special Operations
- **Campaign Budget Allocation**: `POST /api/campaigns/{campaign}/allocate-budget`
- **Campaign Activation**: `POST /api/campaigns/{campaign}/activate`

## 7. Deployment Architecture

### Docker Services
1. **smartlink-app**: PHP 8.4 Laravel application server
2. **smartlink-db**: PostgreSQL 16 database
3. **smartlink-redis**: Redis 7 for caching and queues

### Environment Configuration
- Database connection via environment variables
- Redis configuration for session and cache storage
- Port mappings for all services (8000 for app, 5432 for DB, 6379 for Redis)

## 8. Development Workflow

### Setup Process
1. Clone repository
2. Copy `.env.example` to `.env`
3. Run `composer install`
4. Generate application key with `php artisan key:generate`
5. Start services with `docker-compose up`

### Development Commands
- `composer run dev`: Starts development server with all services
- `php artisan migrate`: Run database migrations
- `php artisan db:seed`: Seed database with test data

## 9. Security Considerations

### Access Control
- All API endpoints protected by authentication
- Ownership middleware ensures users can only access their own resources
- Input validation for all entity operations

### Data Protection
- Password hashing for user authentication
- Environment-based configuration for sensitive data
- Database constraints for data integrity

## 10. Future Considerations

### Scalability
- Current implementation uses development server (artisan serve)
- Directory permissions set to 777 in Dockerfile (security concern)
- Consider production-ready web server (Nginx/Apache) for deployment

### Enhancements
- Implement more comprehensive logging
- Add additional validation rules for business logic
- Extend API with analytics and reporting features
- Implement rate limiting for API endpoints