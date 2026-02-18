# Quick Start Guide - Multi-Tenant SaaS CRM

## Setup Complete! ✅

The multi-tenant SaaS architecture has been successfully set up. Here's what's been created:

## What's Included

### ✅ Database Schema
- Organizations table with soft deletes
- Organization-User pivot with roles (owner, admin, staff, client)
- Plans table (Basic, Pro, Custom)
- Subscriptions table
- Modules table (7 modules)
- Plan-Modules pivot
- Feature Limits table
- Clients table (example tenant-isolated data)

### ✅ Models & Relationships
- User model with organization relationships
- Organization model with subscriptions, users, clients
- Plan model with modules and feature limits
- Subscription model
- Module model
- Client model (example)

### ✅ Middleware
- `SetCurrentOrganization` - Sets organization context
- `EnsureOrganizationHasActiveSubscription` - Validates subscription
- `EnsureModuleAccess` - Validates module access

### ✅ Services & Traits
- `OrganizationPermissionService` - Permission checking logic
- `ChecksOrganizationPermissions` trait - Controller helpers

### ✅ Policies
- `OrganizationPolicy` - Role-based access control

### ✅ Controllers & Routes
- Registration endpoint
- Onboarding flow (organizations, plans)
- Subscription management
- Client management (example module)

### ✅ Seeders
- Plans and Modules seeder (Basic, Pro, Custom plans with modules)

## Quick Test Flow

### 1. Register a User
```bash
curl -X POST http://your-app.test/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'
```

### 2. Create Organization
```bash
curl -X POST http://your-app.test/api/onboarding/organizations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Corp",
    "email": "contact@acme.com"
  }'
```

### 3. Get Available Plans
```bash
curl -X GET http://your-app.test/api/onboarding/plans \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Activate Subscription
```bash
curl -X POST http://your-app.test/api/organizations/1/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Organization-Id: 1" \
  -H "Content-Type: application/json" \
  -d '{
    "plan_id": 1
  }'
```

### 5. Access Module (Client Management)
```bash
curl -X GET http://your-app.test/api/organizations/1/clients \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Organization-Id: 1"
```

## API Endpoints Summary

### Authentication
- `POST /api/register` - Register new user

### Onboarding
- `GET /api/onboarding/plans` - List plans
- `GET /api/onboarding/plans/{plan}` - Get plan details
- `POST /api/onboarding/organizations` - Create organization
- `GET /api/onboarding/organizations` - List user's organizations

### Subscriptions
- `GET /api/organizations/{organization}/subscriptions` - Get current subscription
- `POST /api/organizations/{organization}/subscriptions` - Create subscription
- `DELETE /api/organizations/{organization}/subscriptions` - Cancel subscription

### Client Management Module
- `GET /api/organizations/{organization}/clients` - List clients
- `POST /api/organizations/{organization}/clients` - Create client
- `GET /api/organizations/{organization}/clients/{client}` - Get client
- `PUT /api/organizations/{organization}/clients/{client}` - Update client
- `DELETE /api/organizations/{organization}/clients/{client}` - Delete client

## Organization Context

Organization ID can be provided via:
1. **Header**: `X-Organization-Id: 1`
2. **Query Parameter**: `?organization_id=1`
3. **Route Parameter**: `/organizations/{organization}/...`

## Response Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request (missing organization ID)
- `401` - Unauthenticated
- `402` - Payment Required (subscription/module access required)
- `403` - Forbidden (no access to organization)
- `404` - Not Found

## Plans Configuration

### Basic Plan ($29/month)
- 3 members
- Client Management, Service Management modules
- 50 clients limit

### Pro Plan ($99/month)
- 10 members
- All modules except Staff Management
- 500 clients, 100 invoices/month

### Custom Plan (Custom pricing)
- Unlimited members
- All modules
- Unlimited features

## Adding New Modules

1. Add module to `PlansAndModulesSeeder`
2. Create routes with `EnsureModuleAccess:module-slug` middleware
3. Create controller using `ChecksOrganizationPermissions` trait
4. Add module to plan configurations in seeder

## Example: Creating a New Module Controller

```php
<?php

namespace App\Http\Controllers\Api\YourModule;

use App\Concerns\ChecksOrganizationPermissions;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YourModuleController extends Controller
{
    use ChecksOrganizationPermissions;

    public function index(Request $request, Organization $organization): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'your-module-slug');
        
        // Your logic here
        
        return response()->json(['data' => []]);
    }
}
```

## Next Steps

1. **Add more modules** - Follow the Client Management example
2. **Implement payment integration** - Connect Stripe/PayPal for subscriptions
3. **Add member invitation** - Create endpoints for inviting users
4. **Add role management** - Extend role-based permissions
5. **Add audit logging** - Track organization activities
6. **Add email notifications** - Notify on subscription events

## Documentation

See `MULTI_TENANT_ARCHITECTURE.md` for complete architecture documentation.
