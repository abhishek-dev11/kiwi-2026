<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Customers;
use Illuminate\Support\Facades\Auth;

class FrontUserAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if frontend user is logged in
        if (!Auth::guard('customers')->check()) {
            return redirect()->route('user.login'); // your frontend login route
        }
        return $next($request);
    }
}