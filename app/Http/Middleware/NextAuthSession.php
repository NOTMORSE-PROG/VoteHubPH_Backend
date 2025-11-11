<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class NextAuthSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the NextAuth session token from cookies
        // Access cookies directly from the ParameterBag to bypass Laravel's encryption
        $cookies = $request->cookies->all();

        // Debug: Log the actual cookie values
        \Log::info('Cookie keys:', array_keys($cookies));
        if (isset($cookies['next-auth_session-token'])) {
            \Log::info('Found next-auth_session-token:', ['value' => substr($cookies['next-auth_session-token'], 0, 20) . '...']);
        }

        // Check for user ID in header first (works with both JWT and database sessions)
        $userIdFromHeader = null;
        if ($request->hasHeader('X-User-Id')) {
            $userIdFromHeader = $request->header('X-User-Id');
            \Log::info('Using user ID from header:', ['userId' => $userIdFromHeader]);
        }

        // Try to get session token from cookie
        $sessionToken = $cookies['next-auth.session-token']
                     ?? $cookies['__Secure-next-auth.session-token']
                     ?? $cookies['next-auth_session-token']
                     ?? $cookies['__Host-next-auth.session-token']
                     ?? null;
        
        // If not in cookie, try header (for cases where cookies aren't sent)
        if (!$sessionToken && $request->hasHeader('X-NextAuth-Session-Token')) {
            $sessionToken = $request->header('X-NextAuth-Session-Token');
        }

        // Additional debug
        if (!$sessionToken && !$userIdFromHeader) {
            \Log::error('Session token not found. Available cookies:', [
                'has_next-auth.session-token' => isset($cookies['next-auth.session-token']),
                'has___Secure-next-auth.session-token' => isset($cookies['__Secure-next-auth.session-token']),
                'has_next-auth_session-token' => isset($cookies['next-auth_session-token']),
                'has___Host-next-auth.session-token' => isset($cookies['__Host-next-auth.session-token']),
            ]);

            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'No session token provided',
                'debug_cookies' => array_keys($cookies),
                'debug_checks' => [
                    'next-auth.session-token' => isset($cookies['next-auth.session-token']) ? 'exists' : 'missing',
                    '__Secure-next-auth.session-token' => isset($cookies['__Secure-next-auth.session-token']) ? 'exists' : 'missing',
                    'next-auth_session-token' => isset($cookies['next-auth_session-token']) ? 'exists' : 'missing',
                    '__Host-next-auth.session-token' => isset($cookies['__Host-next-auth.session-token']) ? 'exists' : 'missing',
                ]
            ], 401);
        }

        try {
            $user = null;
            
            // Priority 1: If we have user ID from header, use it directly (works with JWT sessions)
            if ($userIdFromHeader) {
                $user = DB::table('User')
                    ->where('id', $userIdFromHeader)
                    ->first();
                
                if (!$user) {
                    \Log::warning('User not found for header ID:', ['userId' => $userIdFromHeader]);
                    return response()->json([
                        'error' => 'Unauthenticated',
                        'message' => 'User not found'
                    ], 401);
                }
                
                \Log::info('Authenticated user via header:', ['userId' => $user->id, 'email' => $user->email]);
            } else if ($sessionToken) {
                // Priority 2: Try database session lookup (for database sessions)
            // Query the Session table to validate the token
            $session = DB::table('Session')
                ->where('sessionToken', $sessionToken)
                ->where('expires', '>', now())
                ->first();

                \Log::info('Session lookup result:', [
                    'sessionToken' => substr($sessionToken, 0, 20) . '...',
                    'found' => $session ? 'yes' : 'no',
                    'sessionId' => $session->userId ?? 'N/A',
                ]);

            if (!$session) {
                // Check if there are any sessions at all
                $allSessions = DB::table('Session')
                    ->where('expires', '>', now())
                    ->get(['sessionToken', 'userId', 'expires']);
                
                \Log::warning('Session not found. Available sessions:', [
                    'count' => $allSessions->count(),
                    'sessions' => $allSessions->map(function($s) {
                        return [
                            'token' => substr($s->sessionToken, 0, 20) . '...',
                            'userId' => $s->userId,
                            'expires' => $s->expires,
                        ];
                    })->toArray(),
                ]);

                    \Log::warning('Database session not found for token:', [
                        'tokenPrefix' => substr($sessionToken, 0, 20) . '...',
                        'availableSessions' => $allSessions->count()
                    ]);
                    
                return response()->json([
                    'error' => 'Unauthenticated',
                        'message' => 'Invalid or expired session. Please log in again.',
                        'debug' => 'Session token not found in database. With JWT sessions, use X-User-Id header instead.'
                ], 401);
            }

            // Load the user associated with this session
            $user = DB::table('User')
                ->where('id', $session->userId)
                ->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'User not found'
                    ], 401);
                }
                
                \Log::info('Authenticated user via database session:', ['userId' => $user->id, 'email' => $user->email]);
            } else {
                // No authentication method available
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'No authentication provided. Please log in again.'
                ], 401);
            }

            // Attach user to the request for use in controllers
            $request->attributes->set('nextauth_user', $user);
            $request->merge(['authenticated_user_id' => $user->id]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 500);
        }

        return $next($request);
    }
}
