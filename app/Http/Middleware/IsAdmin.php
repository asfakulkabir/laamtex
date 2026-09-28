<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Allows any staff member (super admin or moderator) into the panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->isCustomer()) {
            return redirect()->route('admin.login')
                ->with('error', 'You do not have administrative access.');
        }

        return redirect()->route('admin.login')->with('error', 'Please login to access the admin panel.');
    }
}
