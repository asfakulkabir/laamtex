<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Restricts a route to super admins only (moderators are refused).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->isModerator()) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Only a super admin can access this section.');
        }

        return redirect()->route('admin.login')->with('error', 'You do not have administrative access.');
    }
}
