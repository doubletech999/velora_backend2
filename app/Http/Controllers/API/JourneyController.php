<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\Path;
use App\Services\JourneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    protected JourneyService $journeyService;

    public function __construct(JourneyService $journeyService)
    {
        $this->journeyService = $journeyService;
    }

    /**
     * Get user's journeys
     */
    public function index(Request $request): JsonResponse
    {
        $journeys = $request->user()->journeys()
            ->with(['path.activities'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $journeys,
        ]);
    }

    /**
     * Get active journey
     */
    public function active(Request $request): JsonResponse
    {
        $journey = $this->journeyService->getActiveJourney($request->user());

        if (!$journey) {
            return response()->json([
                'success' => false,
                'message' => 'No active journey found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $journey,
        ]);
    }

    /**
     * Start a new journey
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'path_id' => 'required|exists:paths,id',
        ]);

        try {
            $path = Path::findOrFail($request->path_id);
            $journey = $this->journeyService->startJourney($request->user(), $path);

            return response()->json([
                'success' => true,
                'message' => 'Journey started successfully',
                'data' => $journey->load('path.activities'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Pause journey
     */
    public function pause(Journey $journey): JsonResponse
    {
        $this->authorize('update', $journey);

        try {
            $journey = $this->journeyService->pauseJourney($journey);

            return response()->json([
                'success' => true,
                'message' => 'Journey paused successfully',
                'data' => $journey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resume journey
     */
    public function resume(Journey $journey): JsonResponse
    {
        $this->authorize('update', $journey);

        try {
            $journey = $this->journeyService->resumeJourney($journey);

            return response()->json([
                'success' => true,
                'message' => 'Journey resumed successfully',
                'data' => $journey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete journey
     */
    public function complete(Request $request, Journey $journey): JsonResponse
    {
        $this->authorize('update', $journey);

        $request->validate([
            'distance_traveled' => 'required|numeric|min:0',
            'actual_duration' => 'required|integer|min:0',
            'visited_checkpoints' => 'nullable|integer|min:0',
            'recorded_positions' => 'nullable|array',
            'weather_conditions' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $journey = $this->journeyService->completeJourney($journey, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Journey completed successfully',
                'data' => $journey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Abandon journey
     */
    public function abandon(Journey $journey): JsonResponse
    {
        $this->authorize('update', $journey);

        try {
            $journey = $this->journeyService->abandonJourney($journey);

            return response()->json([
                'success' => true,
                'message' => 'Journey abandoned',
                'data' => $journey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update journey position
     */
    public function updatePosition(Request $request, Journey $journey): JsonResponse
    {
        $this->authorize('update', $journey);

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        try {
            $journey = $this->journeyService->updatePosition($journey, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Position updated',
                'data' => $journey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get journey details
     */
    public function show(Journey $journey): JsonResponse
    {
        $this->authorize('view', $journey);

        $journey->load(['path.activities', 'user']);

        return response()->json([
            'success' => true,
            'data' => $journey,
        ]);
    }

    /**
     * Get journey statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = $this->journeyService->getJourneyStatistics($request->user());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
