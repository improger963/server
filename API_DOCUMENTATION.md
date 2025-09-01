# SmartLink API Documentation

This document provides detailed specifications for all API endpoints in the SmartLink advertising platform.

## Table of Contents

1. [Error Handling](#error-handling)
2. [Authentication](#authentication)
   - [How to Authenticate](#how-to-authenticate)
   - [Token Management](#token-management)
   - [Public Endpoints](#public-endpoints)
   - [Authentication Endpoints](#authentication-endpoints)
     - [Register](#register)
     - [Login](#login)
     - [Logout](#logout)
     - [Forgot Password](#forgot-password)
     - [Reset Password](#reset-password)
     - [Get Authenticated User](#get-authenticated-user)
   - [Public Endpoints Details](#public-endpoints-details)
     - [Public Ad Request](#public-ad-request)
     - [Payeer Webhook](#payeer-webhook)
3. [User Profile Endpoints](#user-profile-endpoints)
   - [Get Referral Statistics](#get-referral-statistics)
4. [Financial Endpoints](#financial-endpoints)
   - [Deposit Funds](#deposit-funds)
   - [Withdraw Funds](#withdraw-funds)
   - [Payeer Webhook](#payeer-webhook-1)
5. [Campaign Endpoints](#campaign-endpoints)
6. [Ad Slot Endpoints](#ad-slot-endpoints)
7. [Analytics Endpoints](#analytics-endpoints)
8. [Notification Endpoints](#notification-endpoints)
9. [Chat Endpoints](#chat-endpoints)
10. [News Endpoints](#news-endpoints)
11. [Ticket Endpoints](#ticket-endpoints)
12. [HTTP Status Codes](#http-status-codes)
13. [Rate Limiting](#rate-limiting)

---

## Error Handling {#error-handling}

The API uses standard HTTP status codes to indicate the success or failure of requests. All error responses follow a consistent JSON format:

```json
{
  "message": "Error description",
  "errors": {  // Optional, present for validation errors
    "field_name": [
      "Error message for field"
    ]
  }
}
```

## Authentication {#authentication}

SmartLink uses Laravel Sanctum for API authentication. Most endpoints require authentication via Bearer tokens.

### How to Authenticate

1. Register a new account or login to an existing account
2. Obtain an API token
3. Include the token in the `Authorization` header of your requests: `Authorization: Bearer <your_token>`

Example:
```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890
```

### Token Management

- Tokens are created during login and can be revoked during logout
- Each token is associated with a specific device name
- Tokens do not expire unless revoked

### Public Endpoints

Some endpoints do not require authentication:
- User registration
- User login
- Password reset requests
- Payeer deposit webhook
- Public ad requests

All other endpoints require a valid authentication token.

### Public Ad Request

Request an ad for display on a website.

**Endpoint:** `GET /api/ad-slots/{adSlot}/request`

**Response Examples:**

Success (200):
```json
{
  "id": 1,
  "ad_slot_id": 1,
  "campaign_id": 1,
  "creative_id": 1,
  "content": {
    "type": "banner",
    "image_url": "https://example.com/banner.jpg",
    "target_url": "https://example.com"
  }
}
```

Not Found (404):
```json
{
  "message": "No active campaigns found for this ad slot"
}
```

### Payeer Webhook

Process deposit notifications from Payeer payment gateway.

**Endpoint:** `POST /api/deposit/payeer-webhook`

**Headers:**
- `Content-Type: application/json`

**Request Body:**
```json
{
  "m_operation_id": "1234567890",
  "m_sign": "signature_hash",
  "m_amount": "100.00",
  "m_curr": "USD",
  "m_orderid": "DEP_1_abc123"
}
```

**Response Examples:**

Success (200):
```json
{
  "status": "success"
}
```

Error (400):
```json
{
  "status": "error",
  "message": "Invalid signature"
}
```

## Authentication Endpoints

### Register

Register a new user account.

**Endpoint:** `POST /api/register`

**Headers:**
- `Content-Type: application/json`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "referral_code": "ABC123XYZ" // Optional
}
```

**Response Examples:**

Success (201):
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "balance": 0,
    "frozen_balance": 0,
    "referrer_id": null,
    "referral_code": "USR000001",
    "email_verified_at": null,
    "created_at": "2025-09-02 10:00:00",
    "updated_at": "2025-09-02 10:00:00"
  }
}
```

Validation Error (422):
```json
{
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

---

### Login

Authenticate a user and obtain an API token.

**Endpoint:** `POST /api/login`

**Headers:**
- `Content-Type: application/json`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "secret123",
  "device_name": "mobile_app" // Optional
}
```

**Response Examples:**

Success (200):
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "balance": 0,
    "frozen_balance": 0,
    "referrer_id": null,
    "referral_code": "USR000001",
    "email_verified_at": null,
    "created_at": "2025-09-02 10:00:00",
    "updated_at": "2025-09-02 10:00:00"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

Invalid Credentials (401):
```json
{
  "message": "Invalid credentials"
}
```

Validation Error (422):
```json
{
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

---

### Logout

Revoke the current API token and logout the user.

**Endpoint:** `POST /api/logout`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Response Examples:**

Success (200):
```json
{
  "message": "Successfully logged out"
}
```

Unauthorized (401):
```json
{
  "message": "Unauthenticated."
}
```

---

### Forgot Password

Send a password reset link to the user's email.

**Endpoint:** `POST /api/forgot-password`

**Headers:**
- `Content-Type: application/json`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response Examples:**

Success (200):
```json
{
  "message": "passwords.sent"
}
```

Validation Error (422):
```json
{
  "errors": {
    "email": [
      "We can't find a user with that email address."
    ]
  }
}
```

---

### Reset Password

Reset the user's password using a reset token.

**Endpoint:** `POST /api/reset-password`

**Headers:**
- `Content-Type: application/json`

**Request Body:**
```json
{
  "token": "reset_token_received_via_email",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response Examples:**

Success (200):
```json
{
  "message": "passwords.reset"
}
```

Validation Error (422):
```json
{
  "errors": {
    "token": [
      "This password reset token is invalid."
    ]
  }
}
```

### Get Authenticated User

Get information about the currently authenticated user.

**Endpoint:** `GET /api/user`

**Headers:**
- `Authorization: Bearer <token>`

**Response Examples:**

Success (200):
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "balance": 0,
  "frozen_balance": 0,
  "referrer_id": null,
  "referral_code": "USR000001",
  "email_verified_at": null,
  "created_at": "2025-09-02T10:00:00.000000Z",
  "updated_at": "2025-09-02T10:00:00.000000Z"
}
```

Unauthorized (401):
```json
{
  "message": "Unauthenticated."
}
```

## User Profile Endpoints {#user-profile-endpoints}

### Get Referral Statistics

Get referral statistics for the current user.

**Endpoint:** `GET /api/profile/referral-stats`

**Headers:**
- `Authorization: Bearer <token>`

**Response Examples:**

Success (200):
```json
{
  "referral_code": "USR000001",
  "referred_users_count": 5,
  "total_earnings": 25.00
}
```

Unauthorized (401):
```json
{
  "message": "Unauthenticated."
}
```

## Financial Endpoints {#financial-endpoints}

### Deposit Funds

Deposit funds into user account.

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

**Response Examples:**

Success (200):
```json
{
  "message": "Deposit successful",
  "balance": 150.00,
  "amount": 100.00
}
```

Validation Error (422):
```json
{
  "errors": {
    "amount": [
      "The amount field is required."
    ]
  }
}
```

Server Error (500):
```json
{
  "error": "Deposit failed: Database connection error"
}
```

---

### Withdraw Funds

Withdraw funds from user account.

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

**Response Examples:**

Success (200):
```json
{
  "message": "Withdrawal request created successfully",
  "balance": 50.00,
  "frozen_balance": 50.00,
  "amount": 50.00
}
```

Insufficient Funds (400):
```json
{
  "error": "Insufficient funds"
}
```

Validation Error (422):
```json
{
  "errors": {
    "amount": [
      "The amount must be at least 1."
    ]
  }
}
```

---

### Payeer Webhook

Process deposit notifications from Payeer payment gateway.

**Endpoint:** `POST /api/deposit/payeer-webhook`

**Headers:**
- `Content-Type: application/json`

**Request Body:**
```json
{
  "m_operation_id": "1234567890",
  "m_sign": "signature_hash",
  "m_amount": "100.00",
  "m_curr": "USD",
  "m_orderid": "DEP_1_abc123"
}
```

**Response Examples:**

Success (200):
```json
{
  "status": "success"
}
```

Error (400):
```json
{
  "status": "error",
  "message": "Invalid signature"
}
```

## Campaign Endpoints {#campaign-endpoints}

### List Campaigns

Get all campaigns for the authenticated user.

**Endpoint:** `GET /api/campaigns`

**Headers:**
- `Authorization: Bearer <token>`

**Response Example (200):**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "name": "Summer Sale",
    "description": "Summer sale campaign",
    "budget": 1000.00,
    "spent": 250.00,
    "start_date": "2025-09-01 00:00:00",
    "end_date": "2025-12-31 23:59:59",
    "is_active": true,
    "created_at": "2025-09-01 10:00:00",
    "updated_at": "2025-09-01 10:00:00"
  }
]
```

---

### Create Campaign

Create a new campaign.

**Endpoint:** `POST /api/campaigns`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "name": "New Campaign",
  "description": "Description of the campaign",
  "budget": 500.00,
  "start_date": "2025-09-01 00:00:00",
  "end_date": "2025-12-31 23:59:59"
}
```

**Response Examples:**

Success (201):
```json
{
  "id": 2,
  "user_id": 1,
  "name": "New Campaign",
  "description": "Description of the campaign",
  "budget": 500.00,
  "spent": 0.00,
  "start_date": "2025-09-01 00:00:00",
  "end_date": "2025-12-31 23:59:59",
  "is_active": true,
  "created_at": "2025-09-01 10:00:00",
  "updated_at": "2025-09-01 10:00:00"
}
```

Validation Error (422):
```json
{
  "errors": {
    "name": [
      "The name field is required."
    ],
    "budget": [
      "The budget must be at least 0."
    ]
  }
}
```

---

### Get Campaign

Get a specific campaign.

**Endpoint:** `GET /api/campaigns/{campaign}`

**Headers:**
- `Authorization: Bearer <token>`

**Response Example (200):**
```json
{
  "id": 1,
  "user_id": 1,
  "name": "Summer Sale",
  "description": "Summer sale campaign",
  "budget": 1000.00,
  "spent": 250.00,
  "start_date": "2025-09-01 00:00:00",
  "end_date": "2025-12-31 23:59:59",
  "is_active": true,
  "created_at": "2025-09-01 10:00:00",
  "updated_at": "2025-09-01 10:00:00"
}
```

---

### Update Campaign

Update a specific campaign.

**Endpoint:** `PUT /api/campaigns/{campaign}`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "name": "Updated Campaign Name",
  "budget": 1500.00
}
```

**Response Example (200):**
```json
{
  "id": 1,
  "user_id": 1,
  "name": "Updated Campaign Name",
  "description": "Summer sale campaign",
  "budget": 1500.00,
  "spent": 250.00,
  "start_date": "2025-09-01 00:00:00",
  "end_date": "2025-12-31 23:59:59",
  "is_active": true,
  "created_at": "2025-09-01 10:00:00",
  "updated_at": "2025-09-01 11:00:00"
}
```

---

### Delete Campaign

Delete a specific campaign.

**Endpoint:** `DELETE /api/campaigns/{campaign}`

**Headers:**
- `Authorization: Bearer <token>`

**Response:**
- 204 No Content (Success)

---

### Allocate Budget to Campaign

Allocate budget from user balance to campaign.

**Endpoint:** `POST /api/campaigns/{campaign}/allocate-budget`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "amount": 200.00
}
```

**Response Examples:**

Success (200):
```json
{
  "message": "Budget allocated successfully",
  "campaign": {
    "id": 1,
    "user_id": 1,
    "name": "Summer Sale",
    "description": "Summer sale campaign",
    "budget": 1200.00,
    "spent": 250.00,
    "start_date": "2025-09-01 00:00:00",
    "end_date": "2025-12-31 23:59:59",
    "is_active": true,
    "created_at": "2025-09-01 10:00:00",
    "updated_at": "2025-09-01 11:00:00"
  },
  "allocated_amount": 200.00
}
```

Insufficient Funds (400):
```json
{
  "error": "Insufficient funds"
}
```

---

### Activate Campaign

Activate a campaign.

**Endpoint:** `POST /api/campaigns/{campaign}/activate`

**Headers:**
- `Authorization: Bearer <token>`

**Response Examples:**

Success (200):
```json
{
  "message": "Campaign activated successfully",
  "campaign": {
    "id": 1,
    "user_id": 1,
    "name": "Summer Sale",
    "description": "Summer sale campaign",
    "budget": 1200.00,
    "spent": 250.00,
    "start_date": "2025-09-01 00:00:00",
    "end_date": "2025-12-31 23:59:59",
    "is_active": true,
    "created_at": "2025-09-01 10:00:00",
    "updated_at": "2025-09-01 11:00:00"
  },
  "is_active": true
}
```

Cannot Activate (400):
```json
{
  "error": "Cannot activate campaign. Check budget and dates."
}
```

---

### Deactivate Campaign

Deactivate a campaign.

**Endpoint:** `POST /api/campaigns/{campaign}/deactivate`

**Headers:**
- `Authorization: Bearer <token>`

**Response Examples:**

Success (200):
```json
{
  "message": "Campaign deactivated successfully",
  "campaign": {
    "id": 1,
    "user_id": 1,
    "name": "Summer Sale",
    "description": "Summer sale campaign",
    "budget": 1200.00,
    "spent": 250.00,
    "start_date": "2025-09-01 00:00:00",
    "end_date": "2025-12-31 23:59:59",
    "is_active": false,
    "created_at": "2025-09-01 10:00:00",
    "updated_at": "2025-09-01 11:00:00"
  },
  "is_active": false
}
```

## Ad Slot Endpoints {#ad-slot-endpoints}

### Request Ad

Request an ad for display in an ad slot (public endpoint).

**Endpoint:** `GET /api/ad-slots/{adSlot}/request`

**Response Examples:**

Success (200):
```json
{
  "creative": {
    "id": 1,
    "campaign_id": 1,
    "type": "image",
    "data": "https://example.com/image.jpg",
    "is_active": true,
    "created_at": "2025-09-01 10:00:00",
    "updated_at": "2025-09-01 10:00:00"
  },
  "campaign_id": 1
}
```

Ad Slot Not Active (410):
```json
{
  "error": "Ad slot is not active"
}
```

No Active Campaigns (404):
```json
{
  "error": "No active campaigns available"
}
```

---

### Associate Campaign with Ad Slot

Associate a campaign with an ad slot.

**Endpoint:** `POST /api/sites/{site}/ad-slots/{adSlot}/campaigns`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "campaign_id": 1
}
```

**Response Examples:**

Success (200):
```json
{
  "message": "Campaign associated successfully"
}
```

Campaign Not Found (404):
```json
{
  "error": "Campaign not found"
}
```

## Analytics Endpoints

### Get Dashboard Statistics

Get analytics dashboard statistics for the authenticated user.

**Endpoint:** `GET /api/stats/dashboard`

**Headers:**
- `Authorization: Bearer <token>`

**Query Parameters:**
- `period` (optional): Filter by time period. Options: `today`, `week`, `month`, `year`. Default: `month`.

**Response Example (200):**
```json
{
  "revenue": 250.00,
  "spend": 1000.00,
  "impressions": 5000,
  "clicks": 250,
  "ctr": 5.00
}
```

## Notification Endpoints {#notification-endpoints}

### List Notifications

Get all notifications for the authenticated user.

**Endpoint:** `GET /api/notifications`

**Headers:**
- `Authorization: Bearer <token>`

**Response Example (200):**
```json
{
  "data": [
    {
      "id": "1",
      "type": "App\\Notifications\\BalanceTopUpSuccess",
      "notifiable_type": "App\\Models\\User",
      "notifiable_id": 1,
      "data": {
        "amount": 100.00,
        "transaction_id": "TXN_12345",
        "balance_after": 250.00
      },
      "read_at": null,
      "created_at": "2025-09-01 10:00:00",
      "updated_at": "2025-09-01 10:00:00"
    }
  ],
  "links": {
    "first": "http://localhost/api/notifications?page=1",
    "last": "http://localhost/api/notifications?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/notifications",
    "per_page": 20,
    "to": 1,
    "total": 1
  }
}
```

---

### Mark Notification as Read

Mark a notification as read.

**Endpoint:** `POST /api/notifications/{id}/read`

**Headers:**
- `Authorization: Bearer <token>`

**Response Examples:**

Success (200):
```json
{
  "message": "Notification marked as read"
}
```

Notification Not Found (404):
```json
{
  "error": "Notification not found"
}
```

---

### Mark All Notifications as Read

Mark all notifications as read.

**Endpoint:** `POST /api/notifications/read-all`

**Headers:**
- `Authorization: Bearer <token>`

**Response:**
```json
{
  "message": "All notifications marked as read"
}
```

## Chat Endpoints {#chat-endpoints}

### Get Chat Messages

Get recent chat messages.

**Endpoint:** `GET /api/chat/messages`

**Headers:**
- `Authorization: Bearer <token>`

**Response Example (200):**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "message": "Hello, world!",
    "created_at": "2025-09-01 10:00:00",
    "updated_at": "2025-09-01 10:00:00",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
]
```

---

### Send Chat Message

Send a chat message.

**Endpoint:** `POST /api/chat/messages`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "message": "Hello, world!"
}
```

**Response Examples:**

Success (201):
```json
{
  "id": 2,
  "user_id": 1,
  "message": "Hello, world!",
  "created_at": "2025-09-01 10:00:00",
  "updated_at": "2025-09-01 10:00:00",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

Validation Error (422):
```json
{
  "errors": {
    "message": [
      "The message field is required."
    ]
  }
}
```

## News Endpoints {#news-endpoints}

### Get Published News

Get all published news items.

**Endpoint:** `GET /api/news`

**Response Example (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "New Feature Release",
      "content": "We're excited to announce the release of our new analytics dashboard...",
      "author": "Jane Smith",
      "is_published": true,
      "published_at": "2025-09-01 10:00:00",
      "created_at": "2025-09-01 09:00:00",
      "updated_at": "2025-09-01 09:00:00"
    }
  ],
  "links": {
    "first": "http://localhost/api/news?page=1",
    "last": "http://localhost/api/news?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/news",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

---

### Admin News Management

Manage news items (admin only).

**Endpoints:**
- `GET /api/admin/news` - List all news items
- `POST /api/admin/news` - Create a news item
- `GET /api/admin/news/{news}` - Get a specific news item
- `PUT /api/admin/news/{news}` - Update a news item
- `DELETE /api/admin/news/{news}` - Delete a news item

## Ticket Endpoints {#ticket-endpoints}

### List Tickets

Get all tickets for the authenticated user.

**Endpoint:** `GET /api/tickets`

**Headers:**
- `Authorization: Bearer <token>`

**Query Parameters:**
- `status` (optional): Filter by status. Options: `open`, `in_progress`, `resolved`, `closed`.
- `priority` (optional): Filter by priority. Options: `low`, `medium`, `high`, `urgent`.
- `category` (optional): Filter by category.

**Response Example (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "subject": "Issue with ad placement",
      "description": "I'm having trouble placing ads on my website...",
      "priority": "high",
      "status": "open",
      "category": "technical",
      "assigned_to": null,
      "created_at": "2025-09-02 10:00:00",
      "updated_at": "2025-09-02 10:00:00",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "replies": []
    }
  ]
}
```

---

### Create Ticket

Create a new support ticket.

**Endpoint:** `POST /api/tickets`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "subject": "Issue with ad placement",
  "description": "I'm having trouble placing ads on my website...",
  "priority": "high",
  "category": "technical"
}
```

**Response Examples:**

Success (201):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "subject": "Issue with ad placement",
    "description": "I'm having trouble placing ads on my website...",
    "priority": "high",
    "status": "open",
    "category": "technical",
    "assigned_to": null,
    "created_at": "2025-09-02 10:00:00",
    "updated_at": "2025-09-02 10:00:00"
  },
  "message": "Ticket created successfully"
}
```

Validation Error (422):
```json
{
  "errors": {
    "subject": [
      "The subject field is required."
    ]
  }
}
```

---

### Get Ticket

Get a specific ticket by ID.

**Endpoint:** `GET /api/tickets/{ticket}`

**Headers:**
- `Authorization: Bearer <token>`

**Response Example (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "subject": "Issue with ad placement",
    "description": "I'm having trouble placing ads on my website...",
    "priority": "high",
    "status": "open",
    "category": "technical",
    "assigned_to": null,
    "created_at": "2025-09-02 10:00:00",
    "updated_at": "2025-09-02 10:00:00",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "replies": [
      {
        "id": 1,
        "ticket_id": 1,
        "user_id": 1,
        "message": "Thanks for reporting this issue. We're looking into it.",
        "created_at": "2025-09-02 11:00:00",
        "updated_at": "2025-09-02 11:00:00",
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      }
    ]
  }
}
```

---

### Add Ticket Reply

Add a reply to a ticket.

**Endpoint:** `POST /api/tickets/{ticket}/reply`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "message": "Thanks for reporting this issue. We're looking into it."
}
```

**Response Examples:**

Success (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "ticket_id": 1,
    "user_id": 1,
    "message": "Thanks for reporting this issue. We're looking into it.",
    "created_at": "2025-09-02 11:00:00",
    "updated_at": "2025-09-02 11:00:00"
  },
  "message": "Reply added successfully"
}
```

Validation Error (422):
```json
{
  "errors": {
    "message": [
      "The message field is required."
    ]
  }
}
```

---

### Update Ticket Status

Update the status of a ticket (admin or assigned user only).

**Endpoint:** `POST /api/tickets/{ticket}/status`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "status": "in_progress"
}
```

**Response Examples:**

Success (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "subject": "Issue with ad placement",
    "description": "I'm having trouble placing ads on my website...",
    "priority": "high",
    "status": "in_progress",
    "category": "technical",
    "assigned_to": null,
    "created_at": "2025-09-02 10:00:00",
    "updated_at": "2025-09-02 12:00:00"
  },
  "message": "Ticket status updated successfully"
}
```

Validation Error (422):
```json
{
  "errors": {
    "status": [
      "The selected status is invalid."
    ]
  }
}
```

---

### Assign Ticket

Assign a ticket to a user (admin only).

**Endpoint:** `POST /api/tickets/{ticket}/assign`

**Headers:**
- `Authorization: Bearer <token>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "user_id": 2
}
```

**Response Examples:**

Success (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "subject": "Issue with ad placement",
    "description": "I'm having trouble placing ads on my website...",
    "priority": "high",
    "status": "open",
    "category": "technical",
    "assigned_to": 2,
    "created_at": "2025-09-02 10:00:00",
    "updated_at": "2025-09-02 12:00:00"
  },
  "message": "Ticket assigned successfully"
}
```

Validation Error (422):
```json
{
  "errors": {
    "user_id": [
      "The selected user is invalid."
    ]
  }
}
```

## Error Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 410 | Gone |
| 422 | Unprocessable Entity |
| 500 | Internal Server Error |

## Rate Limiting {#rate-limiting}

API requests are rate-limited to prevent abuse:
- All requests: 60 per minute
- Financial operations: 
  - Deposit requests: 10 per minute
  - Withdrawal requests: 5 per minute

Exceeding these limits will result in a 429 (Too Many Requests) response.

## HTTP Status Codes {#http-status-codes}

The API uses standard HTTP status codes to indicate the success or failure of requests:

| Code | Description | Meaning |
|------|-------------|---------|
| 200 | OK | Successful GET, PUT, PATCH request |
| 201 | Created | Successful POST request |
| 204 | No Content | Successful DELETE request |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Insufficient permissions for resource |
| 404 | Not Found | Resource not found |
| 405 | Method Not Allowed | HTTP method not allowed for endpoint |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 503 | Service Unavailable | Service temporarily unavailable |