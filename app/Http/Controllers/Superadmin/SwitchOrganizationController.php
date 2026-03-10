<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchOrganizationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(['organization_id' => ['required', 'integer', 'exists:organizations,id']]);

        if (! $request->user()?->isSuperadmin()) {
            abort(403);
        }

        session(['current_organization_id' => $request->organization_id]);

        return redirect()->route('dashboard');
    }
}
