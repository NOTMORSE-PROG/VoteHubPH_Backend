<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PartyListController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleCallback']);
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Helpful GET routes for API endpoints (returns method not allowed message)
Route::get('/auth/send-otp', function () {
    return response()->json([
        'error' => 'This endpoint only accepts POST requests. Please use the registration form to send an OTP.',
        'method' => 'POST',
        'endpoint' => '/api/auth/send-otp',
    ], 405);
});

Route::get('/auth/verify-otp', function () {
    return response()->json([
        'error' => 'This endpoint only accepts POST requests. Please use the registration form to verify your OTP.',
        'method' => 'POST',
        'endpoint' => '/api/auth/verify-otp',
    ], 405);
});

// Public statistics routes
Route::get('/statistics/platform', [StatisticsController::class, 'getPlatformStats']);
Route::get('/statistics/candidates', [StatisticsController::class, 'getCandidateStats']);

// Public posts routes (approved posts for browsing)
// IMPORTANT: Specific routes must come before parameterized routes
Route::get('/posts/approved', [PostController::class, 'getApprovedPosts']);
Route::get('/posts/{id}', [PostController::class, 'show'])->where('id', '[0-9]+'); // Optimized single post with all data - only numeric IDs

// Public comments and votes routes
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/posts/{postId}/vote-status', [VoteController::class, 'getVoteStatus']);

// Public location routes
Route::get('/locations/regions', [LocationController::class, 'getRegions']);
Route::get('/locations/provinces', [LocationController::class, 'getProvinces']);
Route::get('/locations/cities', [LocationController::class, 'getCities']);
Route::get('/locations/districts', [LocationController::class, 'getDistricts']);
Route::get('/locations/barangays', [LocationController::class, 'getBarangays']);

// Public party list routes (read-only)
Route::get('/partylists', [PartyListController::class, 'getPublicPartyLists']);
Route::get('/partylists/{id}', [PartyListController::class, 'show']);
Route::get('/partylists/search', [PartyListController::class, 'search']);

// Admin login (public route)
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// Admin routes (protected with admin authentication)
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/posts', [PostController::class, 'getAllPosts']);
    Route::get('/posts/pending', [PostController::class, 'getPendingPosts']);
    Route::post('/posts/{id}/approve', [PostController::class, 'approve']);
    Route::post('/posts/{id}/reject', [PostController::class, 'reject']);
    
    // Party list routes
    Route::get('/partylists', [PartyListController::class, 'index']);
    Route::get('/partylists/search', [PartyListController::class, 'search']);
    Route::post('/partylists', [PartyListController::class, 'store']);
    Route::post('/partylists/{id}/members', [PartyListController::class, 'addMember']);
    Route::get('/partylists/{id}', [PartyListController::class, 'show']);
});

// Protected routes (require authentication)
Route::middleware('nextauth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/user/complete-profile', [UserController::class, 'completeProfile']);
    Route::put('/user/update', [UserController::class, 'update']);
    Route::delete('/user/delete-account', [UserController::class, 'deleteAccount']);

    // Post routes
    // IMPORTANT: Specific routes must come before parameterized routes
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/my-posts', [PostController::class, 'getUserPosts']);
    Route::put('/posts/{id}', [PostController::class, 'update'])->where('id', '[0-9]+');
    // Comment routes
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::post('/comments/{commentId}/like', [CommentController::class, 'toggleLike']);

    // Vote routes
    Route::post('/posts/{postId}/vote', [VoteController::class, 'vote']);
});
