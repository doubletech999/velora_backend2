<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function show(User $user): JsonResponse
    {
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->load(['achievements' => function($q) {
            $q->whereNotNull('unlocked_at');
        }]);

        // Add statistics
        $user->statistics = [
            'total_journeys' => $user->journeys()->count(),
            'completed_journeys' => $user->journeys()->where('status', 'completed')->count(),
            'total_distance' => $user->journeys()
                ->where('status', 'completed')
                ->sum('distance_traveled'),
            'total_reviews' => $user->reviews()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Get user's journeys
     */
    public function journeys(User $user): JsonResponse
    {
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $journeys = $user->journeys()
            ->with(['path.activities'])
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $journeys,
        ]);
    }

    /**
     * Get user's achievements
     */
    public function achievements(User $user): JsonResponse
    {
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $achievements = $user->achievements()
            ->with('achievement')
            ->whereNotNull('unlocked_at')
            ->orderBy('unlocked_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    /**
     * Get user's reviews
     */
    public function reviews(User $user): JsonResponse
    {
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $reviews = $user->reviews()
            ->with(['path'])
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
