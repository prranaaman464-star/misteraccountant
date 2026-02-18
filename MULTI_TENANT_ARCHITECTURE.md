# Multi-Tenant SaaS Architecture Documentation

## Overview

This document describes the complete multi-tenant SaaS architecture for the CRM system. The system supports multiple organizations, subscription-based access control, and module-based permissions.

## Architecture Components

### 1. Database Schema

#### Core Tables

- **users** - User accounts (extends existing table)
- **organizations** - Tenant organizations
- **organization_user** - Pivot table linking users to organizations with roles
- **plans** - Subscription plans (Basic, Pro, Custom)
- **subscriptions** - Active subscriptions for organizations
- **modules** - Available modules in the system
- **plan_modules** - Pivot table linking plans to enabled modules
- **feature_limits** - Feature limits per plan
- **clients** - Example tenant-isolated data table

#### Key Relationships

```
User -> belongsToMany -> Organization (via organization_user pivot)
Organization -> belongsTo -> User (owner)
Organization -> hasMany -> Subscription
Organization -> hasMany -> Client
Plan -> belongsToMany -> Module (via plan_modules pivot)
Plan -> hasMany -> FeatureLimit
Subscription -> belongsTo -> Organization
Subscription -> belongsTo -> Plan
```

### 2. Models

#### User Model (`app/Models/User.php`)
- Relationships: `organizations()`, `ownedOrganizations()`
- Methods: `belongsToOrganization()`, `getRoleInOrganization()`, `hasRoleInOrganization()`
- Uses Laravel Sanctum for API tokens

#### Organization Model (`app/Models/Organization.php`)
- Relationships: `owner()`, `users()`, `subscriptions()`, `clients()`
- Methods: `activeSubscription()`, `currentPlan()`, `hasActiveSubscription()`
- Soft deletes enabled

#### Plan Model (`app/Models/Plan.php`)
- Relationships: `modules()`, `featureLimits()`, `subscriptions()`
- Methods: `hasModule()`, `getFeatureLimit()`

#### Subscription Model (`app/Models/Subscription.php`)
- Relationships: `organization()`, `plan()`
- Methods: `isActive()`, `isOnTrial()`, `cancel()`

### 3. Middleware

#### SetCurrentOrganization (`app/Http/Middleware/SetCurrentOrganization.php`)
- Sets the current organization context from header/query/route
- Organization ID can be provided via:
  - `X-Organization-Id` header
  - `organization_id` query parameter
  - `{organization}` route parameter

#### EnsureOrganizationHasActiveSubscription (`app/Http/Middleware/EnsureOrganizationHasActiveSubscription.php`)
- Validates organization has active subscription
- Returns 402 (Payment Required) if subscription is inactive
- Includes `requires_upgrade: true` in response

#### EnsureModuleAccess (`app/Http/Middleware/EnsureModuleAccess.php`)
- Validates organization's plan includes the required module
- Takes module slug as parameter: `EnsureModuleAccess:client-management`
- Returns 402 if module not available in plan

### 4. Services

#### OrganizationPermissionService (`app/Services/OrganizationPermissionService.php`)
- `userHasAccess()` - Check user belongs to organization
- `hasActiveSubscription()` - Check subscription status
- `hasModuleAccess()` - Check module availability
- `hasReachedMemberLimit()` - Check member limit
- `canAddMember()` - Check if can add more members
- `checkFeatureLimit()` - Check feature limit
- `getRemainingFeatureLimit()` - Get remaining limit

### 5. Traits

#### ChecksOrganizationPermissions (`app/Concerns/ChecksOrganizationPermissions.php`)
Helper trait for controllers:
- `getCurrentOrganization()` - Get organization from request
- `ensureOrganizationAccess()` - Ensure user has access
- `ensureActiveSubscription()` - Ensure active subscription
- `ensureModuleAccess()` - Ensure module access
- `ensureCanAddMember()` - Ensure can add member
- `ensureFeatureLimit()` - Ensure feature limit not exceeded

### 6. Policies

#### OrganizationPolicy (`app/Policies/OrganizationPolicy.php`)
- `view()` - User belongs to organization
- `update()` - Owner or Admin role
- `delete()` - Owner role only
- `inviteMember()` - Owner or Admin role
- `manageMembers()` - Owner or Admin role

## User Journey Flow

### Step 1: Registration
```
POST /api/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}

Response:
{
    "user": {...},
    "token": "sanctum-token",
    "message": "Registration successful. Please create an organization to continue."
}
```

### Step 2: Create Organization
```
POST /api/onboarding/organizations
Headers: Authorization: Bearer {token}
{
    "name": "Acme Corp",
    "slug": "acme-corp",
    "email": "contact@acme.com"
}

Response:
{
    "organization": {...},
    "message": "Organization created successfully. Please select a plan to continue."
}
```

### Step 3: Select Plan
```
GET /api/onboarding/plans
Headers: Authorization: Bearer {token}

Response:
{
    "plans": [
        {
            "id": 1,
            "name": "Basic",
            "price": 29.00,
            "member_limit": 3,
            "modules": [...],
            "feature_limits": [...]
        },
        ...
    ]
}
```

### Step 4: Activate Subscription
```
POST /api/organizations/{organization_id}/subscriptions
Headers: 
    Authorization: Bearer {token}
    X-Organization-Id: {organization_id}
{
    "plan_id": 1
}

Response:
{
    "subscription": {...},
    "message": "Subscription activated successfully."
}
```

### Step 5: Access Dashboard
```
GET /api/organizations/{organization_id}/clients
Headers: 
    Authorization: Bearer {token}
    X-Organization-Id: {organization_id}

Response:
{
    "clients": [...]
}
```

## API Routes Structure

### Public Routes
- `POST /api/register` - User registration

### Authenticated Routes (auth:sanctum)
- `GET /api/onboarding/plans` - List available plans
- `GET /api/onboarding/plans/{plan}` - Get plan details
- `POST /api/onboarding/organizations` - Create organization
- `GET /api/onboarding/organizations` - List user's organizations

### Organization Routes (with organization context)
- `GET /api/organizations/{organization}/subscriptions` - Get current subscription
- `POST /api/organizations/{organization}/subscriptions` - Create subscription
- `DELETE /api/organizations/{organization}/subscriptions` - Cancel subscription

### Module Routes (with subscription + module checks)
- `GET /api/organizations/{organization}/clients` - List clients
- `POST /api/organizations/{organization}/clients` - Create client
- `GET /api/organizations/{organization}/clients/{client}` - Get client
- `PUT /api/organizations/{organization}/clients/{client}` - Update client
- `DELETE /api/organizations/{organization}/clients/{client}` - Delete client

## Access Control Flow

### Middleware Chain
1. `auth:sanctum` - Authenticate user
2. `SetCurrentOrganization` - Set organization context
3. `EnsureOrganizationHasActiveSubscription` - Check subscription
4. `EnsureModuleAccess:module-slug` - Check module access

### Permission Checking in Controllers
```php
use App\Concerns\ChecksOrganizationPermissions;

class ClientController extends Controller
{
    use ChecksOrganizationPermissions;

    public function store(Request $request, Organization $organization)
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'client-management');
        $this->ensureFeatureLimit($organization, 'clients_limit', $currentCount);
        
        // Proceed with logic
    }
}
```

## Roles

- **owner** - Full access, can delete organization, manage subscriptions
- **admin** - Can manage members, update organization, manage subscriptions
- **staff** - Standard access to enabled modules
- **client** - Read-only access (optional portal access)

## Modules

1. **client-management** - Client Management
2. **service-management** - Service Management
3. **task-compliance** - Task & Compliance
4. **invoice-billing** - Invoice & Billing
5. **reports** - Reports
6. **reminder-system** - Reminder System
7. **staff-management** - Staff Management

## Plans Configuration

### Basic Plan
- Member Limit: 3
- Price: $29/month
- Modules: Client Management, Service Management
- Feature Limits: 50 clients

### Pro Plan
- Member Limit: 10
- Price: $99/month
- Modules: All except Staff Management
- Feature Limits: 500 clients, 100 invoices/month

### Custom Plan
- Member Limit: Unlimited
- Price: Custom
- Modules: All modules
- Feature Limits: Unlimited

## Feature-Based Access Control

### Checking Module Access
```php
$plan = $organization->currentPlan();
if ($plan && $plan->hasModule('client-management')) {
    // Module is available
}
```

### Checking Feature Limits
```php
$service = app(OrganizationPermissionService::class);
$canAdd = $service->canAddMember($organization);
$remaining = $service->getRemainingFeatureLimit($organization, 'clients_limit', $currentCount);
```

### Response Format for Upgrades
```json
{
    "message": "The 'client-management' module is not available in your current plan.",
    "requires_upgrade": true,
    "organization_id": 1,
    "module": "client-management"
}
```

## Data Isolation

All tenant data tables must include `organization_id`:
```php
Schema::create('clients', function (Blueprint $table) {
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    // ... other fields
});
```

Always scope queries by organization:
```php
$clients = $organization->clients()->get();
// or
$clients = Client::where('organization_id', $organization->id)->get();
```

## Setup Instructions

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Seed Plans and Modules**
   ```bash
   php artisan db:seed --class=PlansAndModulesSeeder
   ```

3. **Configure Sanctum**
   - Sanctum config is already published
   - API routes use `auth:sanctum` middleware

4. **Test the Flow**
   - Register a user
   - Create an organization
   - Select and activate a plan
   - Access modules based on plan

## Best Practices

1. **Always check organization access** before any operation
2. **Use middleware** for subscription and module checks
3. **Scope queries** by organization_id
4. **Return 402 status** for upgrade-required scenarios
5. **Use policies** for role-based authorization
6. **Feature-based checks** instead of hardcoded plan checks
7. **Soft deletes** for organizations and clients

## Extending the System

### Adding a New Module

1. Add module to `PlansAndModulesSeeder`
2. Create module routes with `EnsureModuleAccess` middleware
3. Create controller using `ChecksOrganizationPermissions` trait
4. Add module to plan configurations

### Adding a New Plan

1. Add plan to `PlansAndModulesSeeder`
2. Configure modules and feature limits
3. Plans are automatically available via API

### Adding Feature Limits

1. Add to `feature_limits` table via seeder
2. Check limits using `OrganizationPermissionService`
3. Use `ensureFeatureLimit()` in controllers

## Testing

Example test structure:
```php
test('user can create organization', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/onboarding/organizations', [
            'name' => 'Test Org',
        ]);
    
    $response->assertCreated();
    expect($user->organizations)->toHaveCount(1);
});

test('organization without subscription cannot access modules', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user->id, ['role' => 'owner']);
    
    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['X-Organization-Id' => $organization->id])
        ->getJson('/api/organizations/'.$organization->id.'/clients');
    
    $response->assertStatus(402);
});
```
