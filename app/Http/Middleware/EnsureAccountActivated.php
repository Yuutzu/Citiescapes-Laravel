<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActivated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Auto-unlock if lockout period has passed
        $user->autoUnlockIfExpired();

        if ($user->status === 'locked') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account is temporarily locked. Please try again later.');
        }

        if ($user->status === 'archived') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been archived. Please contact the General Manager.');
        }

        // If pending activation, redirect to OTP screen
        if ($user->status === 'pending_activation') {
            return redirect()->route('otp.verify');
        }

        // Force password change after activation
        if ($user->must_change_password && !$request->routeIs('password.change*')) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
