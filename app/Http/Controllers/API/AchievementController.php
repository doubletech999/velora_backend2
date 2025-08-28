<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    protected AchievementService $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Get all achievements
     */
    public function index(Request $request): JsonResponse
    {
        $achievements = Achievement::where('is_active', true)
            ->orderBy('category')
            ->orderBy('points')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    /**
     * Get user's achievements
     */
    public function myAchievements(Request $request): JsonResponse
    {
        $achievements = $this->achievementService->getUserAchievements($request->user());

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    /**
     * Get achievement details
     */
    public function show(Achievement $achievement): JsonResponse
    {
        if (!$achievement->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Achievement not found',
            ], 404);
        }

        // Add user progress if authenticated
        if (auth()->check()) {
            $userAchievement = auth()->user()->achievements()
                ->where('achievement_id', $achievement->id)
                ->first();

            $achievement->user_progress = $userAchievement ? $userAchievement->progress : 0;
            $achievement->unlocked_at = $userAchievement ? $userAchievement->unlocked_at : null;
        }

        return response()->json([
            'success' => true,
            'data' => $achievement,
        ]);
    }

    /**
     * Get leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $leaderboard = $this->achievementService->getLeaderboard($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }
}
