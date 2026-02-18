<?php

namespace App\Http\Requests\Manage;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->currentOrganization();

        if (! $organization) {
            return false;
        }

        return $this->user()->hasRoleInOrganization($organization, 'owner')
            || $this->user()->hasRoleInOrganization($organization, 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organization = $this->currentOrganization();

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_.-]+$/',
                $organization
                    ? Rule::unique('organization_permissions', 'key')
                        ->where('organization_id', $organization->id)
                    : 'unique:organization_permissions,key',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'key.regex' => 'The key may only contain lowercase letters, numbers, dots, underscores and hyphens.',
        ];
    }

    private function currentOrganization(): ?Organization
    {
        $organizationId = session('current_organization_id')
            ?? $this->user()->organizations()->first()?->id;

        if (! $organizationId) {
            return null;
        }

        $org = Organization::find($organizationId);

        if (! $org || ! $this->user()->belongsToOrganization($org)) {
            return null;
        }

        return $org;
    }
}
