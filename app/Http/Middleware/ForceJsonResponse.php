<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ====== FORCE JSON RESPONSE MIDDLEWARE LOGGING ======
        if ($request->isMethod('post') && $request->path() === 'login') {
            \Log::info('========== FORCE JSON RESPONSE MIDDLEWARE - LOGIN REQUEST ==========');
            \Log::info('ForceJsonResponse - Request URL: ' . $request->fullUrl());
            \Log::info('ForceJsonResponse - Request Method: ' . $request->method());
            \Log::info('ForceJsonResponse - Headers before:', [
                'Accept' => $request->header('Accept'),
                'Content-Type' => $request->header('Content-Type'),
                'X-Requested-With' => $request->header('X-Requested-With'),
            ]);
        }
        
        // Force JSON response for all POST requests (React uses POST)
        if ($request->isMethod('post')) {
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('Content-Type', 'application/json');
            
            // Force wantsJson and expectsJson
            if (!$request->expectsJson()) {
                $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            }
            
            if ($request->path() === 'login') {
                \Log::info('ForceJsonResponse - Headers after modification:', [
                    'Accept' => $request->header('Accept'),
                    'Content-Type' => $request->header('Content-Type'),
                    'X-Requested-With' => $request->header('X-Requested-With'),
                ]);
                \Log::info('ForceJsonResponse - Calling next middleware/controller');
            }
        }

        $response = $next($request);
        
        if ($request->isMethod('post') && $request->path() === 'login') {
            \Log::info('ForceJsonResponse - Got response from controller', [
                'status_code' => $response->getStatusCode(),
                'content_type' => $response->headers->get('Content-Type'),
            ]);
        }

        // Ensure JSON response for POST requests - CRITICAL FIX
        if ($request->isMethod('post')) {
            // If response is a redirect, convert it to JSON for POST requests
            if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
                \Log::warning('ForceJsonResponse: POST request got redirect', [
                    'url' => $request->fullUrl(),
                    'redirect_to' => $response->headers->get('Location'),
                    'status' => $response->getStatusCode(),
                ]);
                
                // Return JSON response instead of redirect
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please log in again.',
                    'redirect' => $response->headers->get('Location'),
                ], 401)->header('Content-Type', 'application/json');
            }
            
            // Check if response body is HTML
            $content = $response->getContent();
            $contentType = $response->headers->get('Content-Type');
            
            // If response contains HTML (check for DOCTYPE), it's wrong for POST requests
            if (is_string($content) && (strpos($content, '<!DOCTYPE') !== false || strpos($content, '<html') !== false)) {
                \Log::error('ForceJsonResponse: POST request returned HTML', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'content_type' => $contentType,
                    'status' => $response->getStatusCode(),
                    'content_preview' => substr($content, 0, 200),
                ]);
                
                // Return JSON error instead
                return response()->json([
                    'success' => false,
                    'message' => 'Server returned HTML instead of JSON. Please check your CSRF token and try again.',
                    'debug' => config('app.debug') ? [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'expected_route' => 'POST /login',
                        'content_type_received' => $contentType,
                        'status' => $response->getStatusCode(),
                        'csrf_token_in_request' => $request->header('X-CSRF-TOKEN') ? 'present' : 'missing',
                    ] : null,
                ], 500)->header('Content-Type', 'application/json');
            }
            
            // Always set Content-Type header for JSON responses
            if ($contentType !== 'application/json' && strpos($contentType, 'application/json') === false) {
                $response->headers->set('Content-Type', 'application/json');
            }
        }

        return $response;
    }
}

