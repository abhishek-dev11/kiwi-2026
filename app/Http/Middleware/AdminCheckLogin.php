<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Customers;
use Illuminate\Support\Facades\Auth;

class AdminCheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ( isset(auth()->user()->id)  && isset(auth()->user()->role) ) {
            return  redirect()->route('admin.dashboard');
        } else {
            return $next($request);
        }
    }
}
