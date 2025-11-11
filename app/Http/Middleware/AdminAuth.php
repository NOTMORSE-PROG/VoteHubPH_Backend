<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get user ID from header (for JWT sessions)
        $userIdFromHeader = $request->header('X-User-Id');
        
        // Get session token from cookies
        $cookies = $request->cookies->all();
        $sessionToken = $cookies['next-auth.session-token']
                     ?? $cookies['__Secure-next-auth.session-token']
                     ?? $cookies['next-auth_session-token']
                     ?? $cookies['__Host-next-auth.session-token']
                     ?? null;

        $userId = null;

        // Priority 1: Use header if available
        if ($userIdFromHeader) {
            $userId = $userIdFromHeader;
        } 
        // Priority 2: Get from session
        else if ($sessionToken) {
            $session = DB::table('Session')
                ->where('sessionToken', $sessionToken)
                ->where('expires', '>', now())
                ->first();
            
            if ($session) {
                $userId = $session->userId;
            }
        }

        if (!$userId) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please log in to access admin panel'
            ], 401);
        }

        // Check if user is admin
        $user = DB::table('User')
            ->where('id', $userId)
            ->where('is_admin', true)
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Admin access required'
            ], 403);
        }

        // Add user to request for use in controllers
        $request->merge(['authenticated_admin_id' => $userId]);

        return $next($request);
    }
}
