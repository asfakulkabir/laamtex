<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isCustomer()) {
            return $next($request);
        }

        // guest() stores the requested URL in the session so the login page can
        // send the customer back to where they were heading, instead of always
        // dropping them on the dashboard.
        return redirect()->guest(route('customer.login'))
            ->with('error', 'Please login to access your account.');
    }
}