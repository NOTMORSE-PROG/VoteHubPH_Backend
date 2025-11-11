<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'nextauth' => \App\Http\Middleware\NextAuthSession::class,
            'admin' => \App\Http\Middleware\AdminAuth::class,
        ]);

        // Exclude NextAuth cookies from encryption
        $middleware->encryptCookies(except: [
            'next-auth.session-token',
            '__Secure-next-auth.session-token',
            'next-auth_session-token',
            '__Host-next-auth.session-token',
            'next-auth.csrf-token',
            'next-auth_csrf-token',
            'next-auth.callback-url',
            'next-auth_callback-url',
        ]);

        // CORS configuration
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON errors for API routes
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                \Log::error('API Error: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl(),
                ]);

                // Always include error message in production for debugging
                $errorMessage = $e->getMessage();
                $errorDetails = [
                    'error' => 'Internal Server Error',
                    'message' => $errorMessage,
                ];
                
                // Include more details if it's a database error
                if (str_contains($errorMessage, 'SQLSTATE') || str_contains($errorMessage, 'database') || str_contains($errorMessage, 'table')) {
                    $errorDetails['type'] = 'database_error';
                }
                
                return response()->json($errorDetails, 500);
            }
        });
    })->create();
