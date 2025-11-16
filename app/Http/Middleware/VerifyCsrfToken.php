<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'payment-callback',
        'api/*', // Exempt all API routes from CSRF verification
        // other exempt routes if any
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if ($request->isMethod('post') && $request->path() === 'login') {
            \Log::info('========== CSRF MIDDLEWARE CHECK - LOGIN REQUEST ==========');
            \Log::info('CSRF Middleware - Request URL: ' . $request->fullUrl());
            \Log::info('CSRF Middleware - CSRF Token:', [
                'token_in_header' => $request->header('X-CSRF-TOKEN') ? 'PRESENT' : 'MISSING',
                'token_in_post' => $request->input('_token') ? 'PRESENT' : 'MISSING',
                'session_token' => session()->token() ? 'EXISTS' : 'MISSING',
            ]);
        }

        try {
            $result = parent::handle($request, $next);
            if ($request->isMethod('post') && $request->path() === 'login') {
                \Log::info('CSRF Middleware - CSRF check PASSED');
            }
            return $result;
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            if ($request->isMethod('post') && $request->path() === 'login') {
                \Log::error('CSRF Middleware - CSRF check FAILED', [
                    'error' => $e->getMessage(),
                ]);
            }
            // Always return JSON for POST requests (React uses POST)
            if (
                $request->isMethod('post') ||
                $request->expectsJson() ||
                $request->wantsJson() ||
                $request->ajax() ||
                $request->header('Accept') === 'application/json' ||
                $request->header('X-Requested-With') === 'XMLHttpRequest'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSRF token mismatch. Please refresh the page and try again.',
                ], 419);
            }

            // For regular requests, throw the exception
            throw $e;
        }
    }
}
