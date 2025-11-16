<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $userTypes): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Parse allowed user types (comma-separated)
        $allowedTypes = array_map('trim', explode(',', $userTypes));

        // Check if user has one of the required types
        if (!in_array($user->user_type, $allowedTypes)) {
            // Redirect to appropriate panel based on user type
            switch ($user->user_type) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'client':
                    return redirect()->route('client.dashboard');
                case 'user':
                default:
                    return redirect()->route('user.home');
            }
        }

        return $next($request);
    }
}
