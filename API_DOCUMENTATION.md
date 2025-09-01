# SmartLink API Documentation

This document provides detailed specifications for all API endpoints in the SmartLink advertising platform.

## Authentication

All API endpoints (except Payeer webhook) require authentication via Laravel Sanctum. Include the `Authorization: Bearer <token>` header with your requests.

## Financial Endpoints

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

## Campaign Endpoints

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

## Ad Slot Endpoints

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

## Rate Limiting

Financial operations are rate-limited to prevent abuse:
- Deposit requests: 10 per minute
- Withdrawal requests: 5 per minute

Exceeding these limits will result in a 429 (Too Many Requests) response.