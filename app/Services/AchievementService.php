<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    /**
     * Check and unlock achievements for a user
     */
    public function checkAndUnlockAchievements(User $user): array
    {
        $unlockedAchievements = [];
        $achievements = Achievement::active()->get();

        foreach ($achievements as $achievement) {
            $userAchievement = $this->getUserAchievement($user, $achievement);

            if ($userAchievement && !$userAchievement->isUnlocked()) {
                $progress = $this->calculateProgress($user, $achievement);
                $userAchievement->updateProgress($progress);

                if ($progress >= 100) {
                    $userAchievement->unlock();
                    $unlockedAchievements[] = $achievement;
                    $user->increment('achievements_count');
                }
            }
        }

        return $unlockedAchievements;
    }

    /**
     * Get or create user achievement record
     */
    protected function getUserAchievement(User $user, Achievement $achievement): UserAchievement
    {
        return UserAchievement::firstOrCreate(
            [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
            ],
            [
                'progress' => 0,
            ]
        );
    }

    /**
     * Calculate achievement progress
     */
    protected function calculateProgress(User $user, Achievement $achievement): float
    {
        $requirements = $achievement->requirements;

        switch ($achievement->category) {
            case 'explorer':
                return $this->calculateExplorerProgress($user, $requirements);
            case 'hiker':
                return $this->calculateHikerProgress($user, $requirements);
            case 'region_specific':
                return $this->calculateRegionProgress($user, $requirements);
            case 'challenge':
                return $this->calculateChallengeProgress($user, $requirements);
            default:
                return 0;
        }
    }

    /**
     * Calculate explorer achievement progress
     */
    protected function calculateExplorerProgress(User $user, array $requirements): float
    {
        if (isset($requirements['completed_paths'])) {
            $completed = $user->journeys()
                ->where('status', 'completed')
                ->distinct('path_id')
                ->count('path_id');

            return min(100, ($completed / $requirements['completed_paths']) * 100);
        }

        if (isset($requirements['unique_locations'])) {
            $locations = $user->journeys()
                ->join('paths', 'journeys.path_id', '=', 'paths.id')
                ->where('journeys.status', 'completed')
                ->distinct('paths.location')
                ->count('paths.location');

            return min(100, ($locations / $requirements['unique_locations']) * 100);
        }

        return 0;
    }

    /**
     * Calculate hiker achievement progress
     */
    protected function calculateHikerProgress(User $user, array $requirements): float
    {
        if (isset($requirements['total_distance'])) {
            $distance = $user->journeys()
                ->where('status', 'completed')
                ->sum('distance_traveled');

            return min(100, ($distance / $requirements['total_distance']) * 100);
        }

        if (isset($requirements['total_duration'])) {
            $duration = $user->journeys()
                ->where('status', 'completed')
                ->sum('actual_duration');

            return min(100, ($duration / $requirements['total_duration']) * 100);
        }

        return 0;
    }

    /**
     * Calculate region-specific achievement progress
     */
    protected function calculateRegionProgress(User $user, array $requirements): float
    {
        if (isset($requirements['region']) && isset($requirements['paths_count'])) {
            $completed = $user->journeys()
                ->join('paths', 'journeys.path_id', '=', 'paths.id')
                ->where('journeys.status', 'completed')
                ->where('paths.location', 'LIKE', '%' . $requirements['region'] . '%')
                ->distinct('paths.id')
                ->count('paths.id');

            return min(100, ($completed / $requirements['paths_count']) * 100);
        }

        return 0;
    }

    /**
     * Calculate challenge achievement progress
     */
    protected function calculateChallengeProgress(User $user, array $requirements): float
    {
        if (isset($requirements['difficulty']) && isset($requirements['count'])) {
            $completed = $user->journeys()
                ->join('paths', 'journeys.path_id', '=', 'paths.id')
                ->where('journeys.status', 'completed')
                ->where('paths.difficulty', $requirements['difficulty'])
                ->count();

            return min(100, ($completed / $requirements['count']) * 100);
        }

        if (isset($requirements['consecutive_days'])) {
            $consecutiveDays = $this->getConsecutiveDays($user);
            return min(100, ($consecutiveDays / $requirements['consecutive_days']) * 100);
        }

        return 0;
    }

    /**
     * Get consecutive days of activity
     */
    protected function getConsecutiveDays(User $user): int
    {
        $journeys = $user->journeys()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->pluck('completed_at')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->values();

        if ($journeys->isEmpty()) {
            return 0;
        }

        $consecutive = 1;
        $maxConsecutive = 1;

        for ($i = 1; $i < $journeys->count(); $i++) {
            $prevDate = \Carbon\Carbon::parse($journeys[$i - 1]);
            $currentDate = \Carbon\Carbon::parse($journeys[$i]);

            if ($prevDate->diffInDays($currentDate) === 1) {
                $consecutive++;
                $maxConsecutive = max($maxConsecutive, $consecutive);
            } else {
                $consecutive = 1;
            }
        }

        return $maxConsecutive;
    }

    /**
     * Get user achievements with progress
     */
    public function getUserAchievements(User $user): array
    {
        $achievements = Achievement::active()->get();
        $userAchievements = $user->achievements()->get()->keyBy('achievement_id');

        return $achievements->map(function($achievement) use ($userAchievements, $user) {
            $userAchievement = $userAchievements->get($achievement->id);

            return [
                'achievement' => $achievement,
                'progress' => $userAchievement ? $userAchievement->progress : 0,
                'unlocked_at' => $userAchievement ? $userAchievement->unlocked_at : null,
                'is_unlocked' => $userAchievement ? $userAchievement->isUnlocked() : false,
            ];
        })->toArray();
    }

    /**
     * Get achievement leaderboard
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return User::select('users.*')
            ->selectRaw('COUNT(user_achievements.id) as unlocked_count')
            ->selectRaw('SUM(achievements.points) as total_points')
            ->join('user_achievements', 'users.id', '=', 'user_achievements.user_id')
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->whereNotNull('user_achievements.unlocked_at')
            ->groupBy('users.id')
            ->orderBy('total_points', 'desc')
            ->orderBy('unlocked_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
