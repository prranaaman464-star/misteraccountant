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

#### BindCurrentOrganization (`app/Http/Middleware/BindCurrentOrganization.php`)
- Binds current organization to container for global scopes
- Resolves from: `session('current_organization_id')`, `X-Organization-Id` header, `organization_id` query, or route param
- Superadmin can access any organization

### 4. Global Scopes

#### BelongsToOrganization Trait (`app/Models/Concerns/BelongsToOrganization.php`)
Add to any model that has `organization_id` to automatically scope queries:

```php
use App\Models\Concerns\BelongsToOrganization;

class Client extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;
}
```

#### OrganizationScope (`app/Models/Scopes/OrganizationScope.php`)
- Automatically adds `WHERE organization_id = ?` when `current.organization` is bound to the container
- Only applies when middleware has bound the org (web session or API header/route)
- Bypass with `Client::withoutGlobalScope(OrganizationScope::class)->get()`

### 5. Services

#### OrganizationPermissionService (`app/Services/OrganizationPermissionService.php`)
- `userHasAccess()` - Check user belongs to organization
- `hasActiveSubscription()` - Check subscription status
- `hasModuleAccess()` - Check module availability
- `hasReachedMemberLimit()` - Check member limit
- `canAddMember()` - Check if can add more members
- `checkFeatureLimit()` - Check feature limit
- `getRemainingFeatureLimit()` - Get remaining limit

### 6. Traits

#### ChecksOrganizationPermissions (`app/Concerns/ChecksOrganizationPermissions.php`)
Helper trait for controllers:
- `getCurrentOrganization()` - Get organization from request
- `ensureOrganizationAccess()` - Ensure user has access
- `ensureActiveSubscription()` - Ensure active subscription
- `ensureModuleAccess()` - Ensure module access
- `ensureCanAddMember()` - Ensure can add member
- `ensureFeatureLimit()` - Ensure feature limit not exceeded

### 7. Policies

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

### Migration Template

All tenant data tables must include `organization_id`:

```php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->timestamps();
    $table->index('organization_id');
});
```

### Scoping Options

**Option 1: Global scope (recommended)** – Use `BelongsToOrganization` trait so queries auto-scope:

```php
class Project extends Model
{
    use BelongsToOrganization;
}
```

**Option 2: Relationship** – Use the organization relationship:

```php
$projects = $organization->projects()->get();
```

**Option 3: Manual** – Explicit where clause:

```php
$projects = Project::where('organization_id', $organization->id)->get();
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

## Full Working Example

### 1. Migration

```php
// database/migrations/xxxx_create_projects_table.php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();
    $table->index('organization_id');
});
```

### 2. Model

```php
// app/Models/Project.php
namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use BelongsToOrganization;

    protected $fillable = ['organization_id', 'name', 'description'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
```

### 3. Controller

```php
// app/Http/Controllers/Api/Projects/ProjectController.php
namespace App\Http\Controllers\Api\Projects;

use App\Concerns\ChecksOrganizationPermissions;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ChecksOrganizationPermissions;

    public function index(Request $request, Organization $organization): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $projects = $organization->projects()->latest()->paginate(15);
        return response()->json(['projects' => $projects]);
    }

    public function store(Request $request, Organization $organization): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $validated = $request->validate(['name' => 'required|string', 'description' => 'nullable']);
        $project = $organization->projects()->create($validated);
        return response()->json(['project' => $project], 201);
    }

    public function show(Request $request, Organization $organization, Project $project): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        return response()->json(['project' => $project]);
    }

    public function update(Request $request, Organization $organization, Project $project): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $project->update($request->validate(['name' => 'required|string', 'description' => 'nullable']));
        return response()->json(['project' => $project]);
    }

    public function destroy(Request $request, Organization $organization, Project $project): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $project->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
```

### 4. Add Relationship to Organization

```php
// In app/Models/Organization.php
public function projects(): HasMany
{
    return $this->hasMany(Project::class);
}
```

### 5. Routes

```php
// routes/api.php
Route::middleware(['auth:sanctum', SetCurrentOrganization::class])->group(function () {
    Route::prefix('organizations/{organization}/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update']);
        Route::delete('/{project}', [ProjectController::class, 'destroy']);
    });
});
```

### 6. API Usage

```bash
# List projects (pass org via header or route)
curl -H "Authorization: Bearer {token}" -H "X-Organization-Id: 1" \
  https://app.test/api/organizations/1/projects

# Create project
curl -X POST -H "Authorization: Bearer {token}" -H "X-Organization-Id: 1" \
  -H "Content-Type: application/json" -d '{"name":"My Project"}' \
  https://app.test/api/organizations/1/projects
```

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
