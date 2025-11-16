<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // ====== GUEST MIDDLEWARE LOGGING ======
        \Log::info('========== GUEST MIDDLEWARE CHECK STARTED ==========');
        \Log::info('Guest Middleware - Request URL: ' . $request->fullUrl());
        \Log::info('Guest Middleware - Request Method: ' . $request->method());
        \Log::info('Guest Middleware - Auth Check: ' . (Auth::check() ? 'AUTHENTICATED (User ID: ' . Auth::id() . ')' : 'NOT AUTHENTICATED'));
        
        $guards = empty($guards) ? [null] : $guards;
        \Log::info('Guest Middleware - Guards to check: ' . json_encode($guards));

        foreach ($guards as $guard) {
            $isAuthenticated = Auth::guard($guard)->check();
            \Log::info('Guest Middleware - Checking guard: ' . ($guard ?? 'default'), [
                'is_authenticated' => $isAuthenticated,
                'user_id' => $isAuthenticated ? Auth::guard($guard)->id() : null,
            ]);
            
            if ($isAuthenticated) {
                \Log::info('Guest Middleware - User is authenticated, checking request type');
                // If this is a POST request expecting JSON, return JSON instead of redirecting
                if ($request->isMethod('post') || 
                    $request->expectsJson() || 
                    $request->wantsJson() || 
                    $request->ajax() || 
                    $request->header('Accept') === 'application/json' || 
                    $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    
                    \Log::info('Guest Middleware - Returning JSON for authenticated POST request');
                    
                    $user = Auth::guard($guard)->user();
                    $redirectRoute = route('user.home');
                    
                    if ($user->user_type == 'admin') {
                        $redirectRoute = route('admin.dashboard');
                    } else if ($user->user_type == 'client') {
                        $redirectRoute = route('client.dashboard');
                    }
                    
                    $response = response()->json([
                        'success' => true,
                        'message' => 'You are already authenticated.',
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'user_type' => $user->user_type,
                        ],
                        'redirect' => $redirectRoute,
                    ], 200)->header('Content-Type', 'application/json');
                    
                    \Log::info('Guest Middleware - Response: Already authenticated', ['user_id' => $user->id]);
                    \Log::info('========== GUEST MIDDLEWARE BLOCKED REQUEST (Already Authenticated) ==========');
                    return $response;
                }
                
                \Log::info('Guest Middleware - Redirecting GET request to home');
                // For regular GET requests, redirect to home
                return redirect()->route('user.home');
            }
        }

        \Log::info('Guest Middleware - User not authenticated, allowing request through');
        \Log::info('========== GUEST MIDDLEWARE ALLOWED REQUEST ==========');
        return $next($request);
    }
}

