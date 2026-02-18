<?php

namespace App\Http\Controllers\Api\Clients;

use App\Concerns\ChecksOrganizationPermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Clients\StoreClientRequest;
use App\Models\Client;
use App\Models\Organization;
use App\Services\OrganizationPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use ChecksOrganizationPermissions;

    /**
     * Get all clients for an organization.
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'client-management');

        $clients = $organization->clients()->latest()->paginate(15);

        return response()->json([
            'clients' => $clients,
        ]);
    }

    /**
     * Store a new client.
     */
    public function store(StoreClientRequest $request, Organization $organization): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'client-management');

        // Check feature limit
        $service = app(OrganizationPermissionService::class);
        $currentClientCount = $organization->clients()->count();
        $this->ensureFeatureLimit($organization, 'clients_limit', $currentClientCount);

        $client = $organization->clients()->create($request->validated());

        return response()->json([
            'client' => $client,
            'message' => 'Client created successfully.',
        ], 201);
    }

    /**
     * Get a specific client.
     */
    public function show(Request $request, Organization $organization, Client $client): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'client-management');

        // Ensure client belongs to organization
        if ($client->organization_id !== $organization->id) {
            abort(404);
        }

        return response()->json([
            'client' => $client,
        ]);
    }

    /**
     * Update a client.
     */
    public function update(StoreClientRequest $request, Organization $organization, Client $client): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'client-management');

        // Ensure client belongs to organization
        if ($client->organization_id !== $organization->id) {
            abort(404);
        }

        $client->update($request->validated());

        return response()->json([
            'client' => $client,
            'message' => 'Client updated successfully.',
        ]);
    }

    /**
     * Delete a client.
     */
    public function destroy(Request $request, Organization $organization, Client $client): JsonResponse
    {
        $this->ensureOrganizationAccess($request, $organization);
        $this->ensureActiveSubscription($organization);
        $this->ensureModuleAccess($organization, 'client-management');

        // Ensure client belongs to organization
        if ($client->organization_id !== $organization->id) {
            abort(404);
        }

        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully.',
        ]);
    }
}
