<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PathController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\SavedPathController;
use App\Http\Controllers\API\JourneyController;
use App\Http\Controllers\API\AchievementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Public path routes
    Route::prefix('paths')->group(function () {
        Route::get('/', [PathController::class, 'index']);
        Route::get('/featured', [PathController::class, 'featured']);
        Route::get('/search', [PathController::class, 'search']);
        Route::get('/nearby', [PathController::class, 'nearby']);
        Route::get('/activity/{activity}', [PathController::class, 'byActivity']);
        Route::get('/location', [PathController::class, 'byLocation']);
        Route::get('/{path}', [PathController::class, 'show']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::delete('account', [AuthController::class, 'deleteAccount']);
        });

        // User routes
        Route::prefix('users')->group(function () {
            Route::get('/{user}', [UserController::class, 'show']);
            Route::get('/{user}/journeys', [UserController::class, 'journeys']);
            Route::get('/{user}/achievements', [UserController::class, 'achievements']);
            Route::get('/{user}/reviews', [UserController::class, 'reviews']);
        });

        // Journey routes
        Route::prefix('journeys')->group(function () {
            Route::get('/', [JourneyController::class, 'index']);
            Route::get('/active', [JourneyController::class, 'active']);
            Route::get('/statistics', [JourneyController::class, 'statistics']);
            Route::post('/start', [JourneyController::class, 'start']);
            Route::get('/{journey}', [JourneyController::class, 'show']);
            Route::post('/{journey}/pause', [JourneyController::class, 'pause']);
            Route::post('/{journey}/resume', [JourneyController::class, 'resume']);
            Route::post('/{journey}/complete', [JourneyController::class, 'complete']);
            Route::post('/{journey}/abandon', [JourneyController::class, 'abandon']);
            Route::post('/{journey}/position', [JourneyController::class, 'updatePosition']);
        });

        // Saved paths routes
        Route::prefix('saved-paths')->group(function () {
            Route::get('/', [SavedPathController::class, 'index']);
            Route::post('/{path}', [SavedPathController::class, 'save']);
            Route::delete('/{path}', [SavedPathController::class, 'unsave']);
        });

        // Review routes
        Route::prefix('reviews')->group(function () {
            Route::get('/', [ReviewController::class, 'index']);
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('/{review}', [ReviewController::class, 'update']);
            Route::delete('/{review}', [ReviewController::class, 'destroy']);
        });

        // Achievement routes
        Route::prefix('achievements')->group(function () {
            Route::get('/', [AchievementController::class, 'index']);
            Route::get('/my-achievements', [AchievementController::class, 'myAchievements']);
            Route::get('/leaderboard', [AchievementController::class, 'leaderboard']);
            Route::get('/{achievement}', [AchievementController::class, 'show']);
        });
    });
});
