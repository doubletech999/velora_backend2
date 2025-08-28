<?php

namespace App\Services;

use App\Models\Path;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PathService
{
    /**
     * Get filtered paths with pagination
     */
    public function getFilteredPaths(array $filters, int $perPage = 15)
    {
        $query = Path::with(['activities', 'reviews'])
            ->where('is_active', true);

        // Apply filters
        if (isset($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (isset($filters['activity'])) {
            $query->whereHas('activities', function($q) use ($filters) {
                $q->where('slug', $filters['activity']);
            });
        }

        if (isset($filters['location'])) {
            $query->where(function($q) use ($filters) {
                $q->where('location', 'LIKE', '%'.$filters['location'].'%')
                  ->orWhere('location_ar', 'LIKE', '%'.$filters['location'].'%');
            });
        }

        if (isset($filters['min_length'])) {
            $query->where('length', '>=', $filters['min_length']);
        }

        if (isset($filters['max_length'])) {
            $query->where('length', '<=', $filters['max_length']);
        }

        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get featured paths
     */
    public function getFeaturedPaths(int $limit = 5)
    {
        return Path::with(['activities'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get similar paths
     */
    public function getSimilarPaths(Path $path, int $limit = 4)
    {
        $activityIds = $path->activities->pluck('id');

        return Path::with(['activities'])
            ->where('is_active', true)
            ->where('id', '!=', $path->id)
            ->where(function($q) use ($path, $activityIds) {
                $q->where('difficulty', $path->difficulty)
                  ->orWhereHas('activities', function($q) use ($activityIds) {
                      $q->whereIn('activities.id', $activityIds);
                  });
            })
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new path
     */
    public function createPath(array $data, int $userId): Path
    {
        return DB::transaction(function() use ($data, $userId) {
            $pathData = collect($data)->except(['activities'])->toArray();
            $pathData['created_by'] = $userId;

            $path = Path::create($pathData);

            if (isset($data['activities'])) {
                $path->activities()->attach($data['activities']);
            }

            return $path->fresh(['activities']);
        });
    }

    /**
     * Update a path
     */
    public function updatePath(Path $path, array $data): Path
    {
        return DB::transaction(function() use ($path, $data) {
            $pathData = collect($data)->except(['activities'])->toArray();
            $path->update($pathData);

            if (isset($data['activities'])) {
                $path->activities()->sync($data['activities']);
            }

            return $path->fresh(['activities']);
        });
    }

    /**
     * Search paths
     */
    public function searchPaths(string $query, int $limit = 20)
    {
        return Path::with(['activities'])
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%'.$query.'%')
                  ->orWhere('name_ar', 'LIKE', '%'.$query.'%')
                  ->orWhere('description', 'LIKE', '%'.$query.'%')
                  ->orWhere('description_ar', 'LIKE', '%'.$query.'%')
                  ->orWhere('location', 'LIKE', '%'.$query.'%')
                  ->orWhere('location_ar', 'LIKE', '%'.$query.'%');
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Get path statistics
     */
    public function getPathStatistics(Path $path): array
    {
        $journeys = $path->journeys();

        return [
            'total_journeys' => $journeys->count(),
            'completed_journeys' => $journeys->where('status', 'completed')->count(),
            'average_duration' => $journeys->where('status', 'completed')
                ->avg('actual_duration'),
            'unique_visitors' => $journeys->distinct('user_id')->count('user_id'),
        ];
    }
}
