<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckEmailController extends Controller
{
    /**
     * Check if email exists. If yes redirect to login (with Forgot password).
     * If no redirect to register. SaasyKit-style: email decides login vs signup.
     */
    public function check(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->string('email')->trim()->lower();

        session(['check_email' => (string) $email]);

        $exists = User::where('email', $email)->exists();

        if ($exists) {
            return redirect()->route('login')->with('email', (string) $email);
        }

        return redirect()->route('register')->with('email', (string) $email);
    }
}
