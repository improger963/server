# SmartLink Advertising Platform

SmartLink is a comprehensive digital advertising platform built with Laravel 12 that enables users to create, manage, and monetize online advertising campaigns. The system provides a complete ecosystem for advertisers to run campaigns and publishers to earn revenue by displaying ads on their websites.

## Table of Contents

- [Overview](#overview)
- [Core Features](#core-features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Business Logic](#business-logic)
- [API Endpoints](#api-endpoints)
- [Database Schema](#database-schema)
- [Deployment](#deployment)
- [Development](#development)
- [Testing](#testing)
- [Security](#security)

## Overview

SmartLink is a full-featured advertising platform that connects advertisers with publishers. Advertisers create campaigns with budgets and creatives, while publishers monetize their websites by displaying these ads in ad slots. The platform handles all financial transactions, campaign management, real-time analytics, and user communications.

## Core Features

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

## Technology Stack

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

## Architecture

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

## Business Logic

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

## API Endpoints

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

## Database Schema

### Key Tables
- **users**: User accounts with balance tracking
- **sites**: Advertising sites with URL and status
- **ad_slots**: Ad placements with pricing information
- **campaigns**: Advertising campaigns with budget and scheduling
- **creatives**: Ad content with type and data
- **ad_slot_campaign**: Junction table for ad slot-campaign relationships
- **transaction_logs**: Financial transaction history
- **withdrawals**: Withdrawal requests and processing
- **analytics_events**: User activity and performance tracking
- **chat_messages**: Real-time messaging between users
- **news**: Platform announcements and news
- **user_presence**: User online status tracking
- **referral_earnings**: Referral program earnings tracking

### Relationships
- Users have many Sites, Campaigns, and Creatives (1:N)
- Sites belong to Users and have many AdSlots (1:N)
- AdSlots belong to Sites and have many Campaigns (N:M)
- Campaigns belong to Users and have many Creatives (1:N)
- Creatives belong to Campaigns (N:1)
- Users have many TransactionLogs and Withdrawals (1:N)
- AnalyticsEvents belong to Users (N:1)
- ChatMessages belong to Users (N:1)
- ReferralEarnings belong to Users (N:1)

## Deployment

### Docker Services
1. **smartlink-app**: PHP 8.4 Laravel application server
2. **smartlink-reverb**: Laravel Reverb WebSocket server
3. **smartlink-db**: PostgreSQL 16 database
4. **smartlink-redis**: Redis 7 for caching and queues

### Environment Configuration
- Database connection via environment variables
- Redis configuration for session and cache storage
- Port mappings for all services (8000 for app, 8080 for Reverb, 5432 for DB, 6379 for Redis)

### Setup Process
1. Clone repository
2. Copy `.env.example` to `.env` and configure environment variables
3. Run `composer install`
4. Generate application key with `php artisan key:generate`
5. Start services with `docker-compose up`

## Development

### Development Commands
- `composer run dev`: Starts development server with all services
- `php artisan migrate`: Run database migrations
- `php artisan db:seed`: Seed database with test data

### Code Structure
- **app/Models**: Eloquent models representing database entities
- **app/Http/Controllers**: API controllers handling HTTP requests
- **app/Services**: Business logic services
- **app/Jobs**: Background jobs for scheduled tasks
- **app/Events**: Event classes for broadcasting
- **app/Notifications**: Notification classes
- **database/migrations**: Database schema migrations
- **database/factories**: Model factories for testing
- **tests**: Unit and feature tests

## Testing

### Unit Tests
Unit tests cover individual components and business logic:
- Model tests for User, Campaign, AdSlot, Creative, etc.
- Service tests for CampaignService, FinancialService, AdSlotService, etc.
- Middleware tests for ownership validation

### Feature Tests
Feature tests cover API endpoints and user workflows:
- Authentication tests for registration and login
- Resource management tests for CRUD operations
- Financial tests for deposit and withdrawal workflows
- Real-time feature tests for chat and notifications

### Test Commands
- `php artisan test`: Run all tests
- `php artisan test --filter TestName`: Run specific test

## Security

### Access Control
- All API endpoints protected by authentication
- Ownership middleware ensures users can only access their own resources
- Input validation for all entity operations

### Data Protection
- Password hashing for user authentication
- Environment-based configuration for sensitive data
- Database constraints for data integrity
- SQL injection prevention through Eloquent ORM

### Rate Limiting
- Financial operations are rate-limited to prevent abuse
- Deposit requests: 10 per minute
- Withdrawal requests: 5 per minute
</parameter_content>