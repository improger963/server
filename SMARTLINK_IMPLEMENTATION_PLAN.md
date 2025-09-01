# SmartLink Business Logic Implementation Plan

This document outlines the implementation plan for the SmartLink advertising platform based on the design document and current codebase analysis.

## Project Overview

The SmartLink platform is a Laravel 12 application with PHP 8.4, PostgreSQL database, and Redis for caching/queues. The system already has a solid foundation with User, Site, AdSlot, Campaign, and Creative models, along with appropriate middleware for ownership validation.

## Implementation Phases

### Phase 1: Data Model Enhancements and Core Service Updates

#### Task 1: Enhance User Model with Frozen Balance
- Add `frozen_balance` field to users table via migration
- Add methods to User model: freezeBalance(), unfreezeBalance()
- Update User model with proper validation and error handling

#### Task 2: Create Withdrawal Model
- Create `app/Models/Withdrawal.php` with fields: id, user_id, amount, status, processed_at
- Add relationship to User model
- Implement withdrawal statuses: pending, approved, rejected, processed

#### Task 3: Enhance Campaign Model
- Add methods: checkIfExpired(), returnUnusedBudget(), getRemainingBudget()
- Enhance existing methods with better error handling and validation
- Add proper indexing for performance

#### Task 4: Create Transaction Log Model
- Create `app/Models/TransactionLog.php` with fields: id, user_id, amount, type, reference, status, description
- Add relationship to User model
- Implement transaction types: deposit, withdrawal, budget_allocation, budget_return, impression_charge

### Phase 2: Financial System Integration (Payeer, Withdrawals)

#### Task 5: Create PayeerService
- Create `app/Services/PayeerService.php` to handle Payeer API interactions
- Implement methods: initiateDeposit, verifyWebhook, processDeposit
- Add error handling for invalid signatures, duplicate transactions, amount mismatches

#### Task 6: Implement Payeer Webhook Endpoint
- Add webhook endpoint `POST /api/deposit/payeer-webhook` in FinancialController
- Implement signature verification for webhook security
- Add proper error responses and logging

#### Task 7: Add Transaction Logging
- Implement comprehensive transaction logging for all deposit operations
- Add verification mechanisms to prevent duplicate transactions
- Include IP address and user agent tracking for security

#### Task 8: Implement Atomic Balance Updates
- Use database transactions to ensure atomicity of balance updates
- Add proper rollback mechanisms for failed transactions
- Implement retry logic for transient failures

#### Task 9: Modify User Model for Frozen Balances
- Enhance User model to handle frozen balances with freezeBalance() and unfreezeBalance() methods
- Add validation to prevent operations on frozen amounts
- Implement proper error handling for insufficient funds scenarios

#### Task 10: Implement Withdrawal Request Creation
- Add withdrawal request creation in FinancialController
- Implement proper validation for withdrawal amounts
- Add rate limiting to prevent abuse

#### Task 11: Add Withdrawal Approval/Rejection Logic
- Implement withdrawal approval/rejection logic in FinancialService
- Add admin notification mechanisms
- Implement audit logging for all approval/rejection actions

#### Task 12: Create Withdrawal Validation
- Create validation to prevent withdrawals exceeding available balance
- Implement daily/weekly withdrawal limits
- Add fraud detection mechanisms

### Phase 3: Campaign Lifecycle Automation

#### Task 13: Enhance CampaignService
- Enhance CampaignService with real-time budget tracking capabilities
- Add methods: checkBudget(), deactivateExpired()
- Implement proper error handling and logging

#### Task 14: Implement Scheduled Budget Checks
- Implement scheduled budget checks for campaign expiration
- Add proper error handling and retry mechanisms
- Implement notification system for budget exhaustion

#### Task 15: Add Budget Exhaustion Handling
- Add budget exhaustion handling in CampaignService
- Implement automatic campaign deactivation
- Add user notification mechanisms

#### Task 21: Create CampaignDeactivationJob
- Create `app/Jobs/CampaignDeactivationJob.php`
- Implement campaign status checking logic
- Add proper error handling and logging

#### Task 22: Schedule CampaignDeactivationJob
- Schedule job to run daily via Laravel scheduler in `app/Console/Kernel.php`
- Add configuration options for scheduling frequency
- Implement monitoring and alerting for job failures

#### Task 23: Implement Campaign Status Checking
- Implement campaign status checking logic for expired and budget-exhausted campaigns
- Add batch processing for efficiency
- Implement proper error handling and retry mechanisms

#### Task 24: Add Budget Return Functionality
- Add budget return functionality to return unused funds to user balance
- Implement proper transaction logging
- Add audit trail for all budget returns

#### Task 35: Create BudgetMonitoringJob
- Create BudgetMonitoringJob for real-time campaign budget monitoring
- Schedule to run every 15 minutes
- Implement efficient querying and minimal resource usage

### Phase 4: Ad Serving Logic and Micro-Payments

#### Task 16: Implement Real-Time Budget Deduction
- Implement real-time budget deduction in AdSlotService when ads are served
- Use atomic operations to prevent race conditions
- Add proper error handling and rollback mechanisms

#### Task 25: Enhance AdSlotService
- Enhance AdSlotService with request processing capabilities
- Add method: processAdRequest(AdSlot $adSlot)
- Implement proper error handling and logging

#### Task 26: Implement Campaign Selection Algorithm
- Implement campaign selection algorithm based on budget and activity status
- Add weighting mechanisms for fair campaign distribution
- Implement proper error handling and fallback mechanisms

#### Task 27: Add Creative Selection Logic
- Add creative selection logic to choose random creative from active campaigns
- Implement creative rotation algorithms
- Add proper error handling for cases with no available creatives

#### Task 28: Integrate Micro-Payment System
- Integrate micro-payment system for impression deductions
- Implement proper transaction logging
- Add audit trail for all micro-payments

#### Task 33: Implement Ad Request Processing Endpoint
- Implement ad request processing endpoint in AdSlotController
- Add proper error handling for various failure scenarios
- Implement rate limiting to prevent abuse

#### Task 34: Implement Campaign Association Endpoint
- Implement campaign association endpoint in AdSlotController
- Add proper validation for campaign and ad slot compatibility
- Implement proper error handling and logging

### Phase 5: Testing, Documentation, and Validation

#### Task 17: Create SiteOwnershipMiddleware Tests
- Create unit tests for SiteOwnershipMiddleware with positive and negative scenarios
- Test owner access (200 OK) and non-owner access (403 Forbidden)
- Test invalid entity ID (404 Not Found) and deleted entity (404 Not Found)

#### Task 18: Create CampaignOwnershipMiddleware Tests
- Create unit tests for CampaignOwnershipMiddleware with positive and negative scenarios
- Test owner access (200 OK) and non-owner access (403 Forbidden)
- Test invalid entity ID (404 Not Found) and deleted entity (404 Not Found)

#### Task 19: Create AdSlotOwnershipMiddleware Tests
- Create unit tests for AdSlotOwnershipMiddleware with positive and negative scenarios
- Test owner access (200 OK) and non-owner access (403 Forbidden)
- Test invalid entity ID (404 Not Found) and deleted entity (404 Not Found)

#### Task 20: Create CreativeOwnershipMiddleware Tests
- Create unit tests for CreativeOwnershipMiddleware with positive and negative scenarios
- Test owner access (200 OK) and non-owner access (403 Forbidden)
- Test invalid entity ID (404 Not Found) and deleted entity (404 Not Found)

#### Task 29: Implement Deposit Endpoint
- Implement deposit endpoint in FinancialController with proper validation
- Add comprehensive error handling
- Implement proper response codes and messages

#### Task 30: Implement Withdrawal Endpoint
- Implement withdrawal endpoint in FinancialController with proper validation
- Add comprehensive error handling
- Implement proper response codes and messages

#### Task 31: Implement Campaign Budget Allocation Endpoint
- Implement campaign budget allocation endpoint in CampaignController
- Add comprehensive error handling
- Implement proper response codes and messages

#### Task 32: Implement Campaign Activation/Deactivation Endpoints
- Implement campaign activation/deactivation endpoints in CampaignController
- Add comprehensive error handling
- Implement proper response codes and messages

#### Task 36: Add Error Handling for Financial Operations
- Add comprehensive error handling and validation for all financial operations
- Implement proper logging for all error scenarios
- Add user-friendly error messages

#### Task 37: Add Error Handling for Campaign Operations
- Add comprehensive error handling and validation for all campaign operations
- Implement proper logging for all error scenarios
- Add user-friendly error messages

#### Task 38: Implement Security Measures
- Implement security measures including ownership validation and authentication
- Add rate limiting for financial operations
- Implement input sanitization and validation
- Ensure SQL injection prevention through Eloquent ORM

#### Task 39: Document API Endpoints
- Document all API endpoints with detailed specifications
- Add example requests and responses
- Include error response codes and descriptions

#### Task 40: Perform Integration Testing
- Perform integration testing of all components
- Test end-to-end workflows
- Validate data consistency across all operations

#### Task 41: Conduct Final Validation
- Conduct final validation and documentation review
- Perform security audit
- Validate performance under load
- Ensure all requirements are met

## API Endpoints ("Tablets of API")

### Financial Endpoints

#### Deposit Endpoint
- Method: POST
- URL: `/api/deposit`
- Headers: Authorization: Bearer <token>
- Request Body: amount (numeric)
- Responses: 200, 400, 401, 422

#### Withdrawal Endpoint
- Method: POST
- URL: `/api/withdraw`
- Headers: Authorization: Bearer <token>
- Request Body: amount (numeric)
- Responses: 200, 400, 401, 422

#### Payeer Webhook Endpoint
- Method: POST
- URL: `/api/deposit/payeer-webhook`
- Headers: None (validated via signature)
- Request Body: Payeer transaction data
- Responses: 200, 400, 401, 422

### Campaign Endpoints

#### Campaign Budget Allocation
- Method: POST
- URL: `/api/campaigns/{campaign}/allocate-budget`
- Headers: Authorization: Bearer <token>
- Request Body: amount (numeric)
- Responses: 200, 400, 401, 403, 422

#### Campaign Activation
- Method: POST
- URL: `/api/campaigns/{campaign}/activate`
- Headers: Authorization: Bearer <token>
- Request Body: none
- Responses: 200, 400, 401, 403

#### Campaign Deactivation
- Method: POST
- URL: `/api/campaigns/{campaign}/deactivate`
- Headers: Authorization: Bearer <token>
- Request Body: none
- Responses: 200, 400, 401, 403

### Ad Slot Endpoints

#### Ad Request Processing
- Method: GET
- URL: `/api/ad-slots/{adSlot}/request`
- Headers: none (public endpoint)
- Parameters: none
- Responses: 200, 404, 410

#### Campaign Association
- Method: POST
- URL: `/api/ad-slots/{adSlot}/campaigns`
- Headers: Authorization: Bearer <token>
- Request Body: campaign_id
- Responses: 200, 400, 401, 403, 422

## Implementation Sequence

1. Enhance data models with new fields and relationships
2. Implement PayeerService and webhook integration
3. Develop FinancialService for withdrawal processing
4. Enhance CampaignService with improved budget management
5. Implement scheduled jobs for campaign lifecycle management
6. Enhance AdSlotService with ad request processing logic
7. Create comprehensive unit tests for middleware
8. Document all API endpoints with detailed specifications
9. Perform integration testing of all components
10. Final validation and documentation review

## Success Criteria

- All financial operations are atomic and secure
- Campaign lifecycle is fully automated
- Ad serving logic works correctly with micro-payments
- All middleware properly validates ownership
- Comprehensive test coverage for all components
- Proper documentation for all API endpoints
- Successful integration testing of all workflows