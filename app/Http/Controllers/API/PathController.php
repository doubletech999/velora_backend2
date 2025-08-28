<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Path;
use App\Services\PathService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PathController extends Controller
{
    protected PathService $pathService;

    public function __construct(PathService $pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * Get all paths with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'difficulty', 'activity', 'location',
            'min_length', 'max_length', 'min_rating',
            'sort_by', 'sort_order'
        ]);

        $paths = $this->pathService->getFilteredPaths($filters);

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }

    /**
     * Get featured paths
     */
    public function featured(): JsonResponse
    {
        $paths = $this->pathService->getFeaturedPaths();

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }

    /**
     * Search paths
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $paths = $this->pathService->searchPaths($request->query);

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }

    /**
     * Get path details
     */
    public function show(Path $path): JsonResponse
    {
        if (!$path->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Path not found',
            ], 404);
        }

        $path->load(['activities', 'reviews.user', 'createdBy']);

        // Add user-specific data if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $path->is_saved = $user->savedPaths()->where('path_id', $path->id)->exists();
            $path->user_journey = $user->journeys()
                ->where('path_id', $path->id)
                ->latest()
                ->first();
            $path->user_review = $user->reviews()
                ->where('path_id', $path->id)
                ->first();
        }

        // Get statistics
        $path->statistics = $this->pathService->getPathStatistics($path);

        // Get similar paths
        $path->similar_paths = $this->pathService->getSimilarPaths($path);

        return response()->json([
            'success' => true,
            'data' => $path,
        ]);
    }

    /**
     * Get paths by activity
     */
    public function byActivity(string $activitySlug): JsonResponse
    {
        $paths = Path::with(['activities'])
            ->whereHas('activities', function($q) use ($activitySlug) {
                $q->where('slug', $activitySlug);
            })
            ->where('is_active', true)
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }

    /**
     * Get paths by location
     */
    public function byLocation(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'required|string',
        ]);

        $paths = Path::with(['activities'])
            ->where(function($q) use ($request) {
                $q->where('location', 'LIKE', '%'.$request->location.'%')
                  ->orWhere('location_ar', 'LIKE', '%'.$request->location.'%');
            })
            ->where('is_active', true)
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }

    /**
     * Get nearby paths
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100', // km
        ]);

        $radius = $request->radius ?? 10; // default 10km

        // Using Haversine formula for distance calculation
        $paths = Path::selectRaw("*,
            ( 6371 * acos( cos( radians(?) ) *
            cos( radians( JSON_EXTRACT(coordinates, '$[0].lat') ) ) *
            cos( radians( JSON_EXTRACT(coordinates, '$[0].lng') ) - radians(?) ) +
            sin( radians(?) ) *
            sin( radians( JSON_EXTRACT(coordinates, '$[0].lat') ) ) ) ) AS distance",
            [$request->latitude, $request->longitude, $request->latitude])
            ->having('distance', '<', $radius)
            ->where('is_active', true)
            ->orderBy('distance')
            ->with(['activities'])
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }
}
