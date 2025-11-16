<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add ForceJsonResponse middleware to web group for POST requests
        $middleware->web(append: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
        
        // Enable Sanctum stateful authentication for API routes from same domain
        // This allows session-based auth to work with API routes
        $middleware->statefulApi();
        
        // Add CORS middleware for API routes (for Flutter mobile apps)
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        $middleware->alias([
            'user.type' => \App\Http\Middleware\CheckUserType::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON responses for AJAX/API requests and POST requests (React)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            // Always return JSON for POST requests (React uses POST)
            if ($request->isMethod('post') || 
                $request->expectsJson() || 
                $request->wantsJson() || 
                $request->ajax() || 
                $request->header('Accept') === 'application/json' || 
                $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
        
        $exceptions->render(function (\Exception $e, \Illuminate\Http\Request $request) {
            // Always return JSON for POST requests (React uses POST)
            if ($request->isMethod('post') || 
                $request->expectsJson() || 
                $request->wantsJson() || 
                $request->ajax() || 
                $request->header('Accept') === 'application/json' || 
                $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred.',
                    'error' => config('app.debug') ? [
                        'type' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], 500);
            }
        });
    })->create();
