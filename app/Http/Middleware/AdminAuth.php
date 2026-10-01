<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Route;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Customers;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        
        if ( isset(auth()->user()->id) && isset(auth()->user()->role) ) {
            $id = Auth::id();
            return $next($request);
        } else {
            return  redirect()->route('admin.login');
        }
    }
}
