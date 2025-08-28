<?php

namespace App\Services;

use App\Models\Journey;
use App\Models\Path;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JourneyService
{
    protected AchievementService $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Start a new journey
     */
    public function startJourney(User $user, Path $path): Journey
    {
        // Check for active journey
        $activeJourney = $this->getActiveJourney($user);
        if ($activeJourney) {
            throw new \Exception('You already have an active journey');
        }

        return Journey::create([
            'user_id' => $user->id,
            'path_id' => $path->id,
            'status' => 'started',
            'started_at' => now(),
        ]);
    }

    /**
     * Get user's active journey
     */
    public function getActiveJourney(User $user): ?Journey
    {
        return Journey::where('user_id', $user->id)
            ->whereIn('status', ['started', 'paused'])
            ->with('path.activities')
            ->first();
    }

    /**
     * Pause a journey
     */
    public function pauseJourney(Journey $journey): Journey
    {
        if ($journey->status !== 'started') {
            throw new \Exception('Journey is not active');
        }

        $journey->update(['status' => 'paused']);
        return $journey;
    }

    /**
     * Resume a journey
     */
    public function resumeJourney(Journey $journey): Journey
    {
        if ($journey->status !== 'paused') {
            throw new \Exception('Journey is not paused');
        }

        $journey->update(['status' => 'started']);
        return $journey;
    }

    /**
     * Complete a journey
     */
    public function completeJourney(Journey $journey, array $data): Journey
    {
        return DB::transaction(function() use ($journey, $data) {
            $journey->update([
                'status' => 'completed',
                'completed_at' => now(),
                'distance_traveled' => $data['distance_traveled'],
                'actual_duration' => $data['actual_duration'],
                'visited_checkpoints' => $data['visited_checkpoints'] ?? 0,
                'recorded_positions' => $data['recorded_positions'] ?? [],
                'weather_conditions' => $data['weather_conditions'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update user statistics
            $user = $journey->user;
            $user->increment('completed_trips');

            // Check for achievements
            $this->achievementService->checkAndUnlockAchievements($user);

            return $journey;
        });
    }

    /**
     * Abandon a journey
     */
    public function abandonJourney(Journey $journey): Journey
    {
        if (!in_array($journey->status, ['started', 'paused'])) {
            throw new \Exception('Journey cannot be abandoned');
        }

        $journey->update([
            'status' => 'abandoned',
        ]);

        return $journey;
    }

    /**
     * Update journey position
     */
    public function updatePosition(Journey $journey, array $position): Journey
    {
        if (!in_array($journey->status, ['started'])) {
            throw new \Exception('Journey is not active');
        }

        $positions = $journey->recorded_positions ?? [];
        $positions[] = array_merge($position, ['timestamp' => now()->toIso8601String()]);

        $journey->update([
            'recorded_positions' => $positions,
        ]);

        return $journey;
    }

    /**
     * Get journey statistics
     */
    public function getJourneyStatistics(User $user): array
    {
        $journeys = $user->journeys();

        return [
            'total_journeys' => $journeys->count(),
            'completed_journeys' => $journeys->where('status', 'completed')->count(),
            'total_distance' => $journeys->where('status', 'completed')
                ->sum('distance_traveled'),
            'total_time' => $journeys->where('status', 'completed')
                ->sum('actual_duration'),
            'favorite_difficulty' => $this->getFavoriteDifficulty($user),
            'monthly_stats' => $this->getMonthlyStats($user),
        ];
    }

    /**
     * Get user's favorite difficulty
     */
    protected function getFavoriteDifficulty(User $user): ?string
    {
        return Journey::join('paths', 'journeys.path_id', '=', 'paths.id')
            ->where('journeys.user_id', $user->id)
            ->where('journeys.status', 'completed')
            ->select('paths.difficulty', DB::raw('count(*) as count'))
            ->groupBy('paths.difficulty')
            ->orderBy('count', 'desc')
            ->first()?->difficulty;
    }

    /**
     * Get monthly statistics
     */
    protected function getMonthlyStats(User $user): array
    {
        $stats = [];
        $currentDate = Carbon::now();

        for ($i = 5; $i >= 0; $i--) {
            $month = $currentDate->copy()->subMonths($i);
            $stats[] = [
                'month' => $month->format('M Y'),
                'completed' => Journey::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->whereYear('completed_at', $month->year)
                    ->whereMonth('completed_at', $month->month)
                    ->count(),
                'distance' => Journey::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->whereYear('completed_at', $month->year)
                    ->whereMonth('completed_at', $month->month)
                    ->sum('distance_traveled'),
            ];
        }

        return $stats;
    }
}
