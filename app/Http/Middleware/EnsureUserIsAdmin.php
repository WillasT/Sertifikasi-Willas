<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is NOT an admin, block them with a 403 Forbidden error
        if ($request->user()->account_type !== 'admin') {
            abort(403, 'Unauthorized access. Admins only.');
        }

        // Otherwise, let them pass through to the page
        return $next($request);
    }
}