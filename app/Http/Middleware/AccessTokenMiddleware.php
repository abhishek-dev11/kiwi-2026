<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AccessTokenMiddleware
{

    public function handle($request, Closure $next)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
        
            if (!$user = Auth::guard('api')->user()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $Customers = Customers::where('id', $user->id)->where('status', 'Active')->whereNull('is_delete')->first();
            if (!$Customers) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }
            
            // dd(JWTAuth::getPayload()->get('password'),$Customers->password);
            if (JWTAuth::getPayload()->get('email') != $Customers->email) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }


            if (JWTAuth::getPayload()->get('password') != $Customers->password) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
    }
}
