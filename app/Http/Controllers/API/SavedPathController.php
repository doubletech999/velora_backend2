<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Path;
use App\Models\SavedPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedPathController extends Controller
{
    /**
     * Get user's saved paths
     */
    public function index(Request $request): JsonResponse
    {
        $savedPaths = $request->user()->savedPaths()
            ->with(['activities'])
            ->orderBy('saved_paths.created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $savedPaths,
        ]);
    }

    /**
     * Save a path
     */
    public function save(Request $request, Path $path): JsonResponse
    {
        if (!$path->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Path not found',
            ], 404);
        }

        $user = $request->user();

        // Check if already saved
        $exists = SavedPath::where('user_id', $user->id)
            ->where('path_id', $path->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Path already saved',
            ], 400);
        }

        SavedPath::create([
            'user_id' => $user->id,
            'path_id' => $path->id,
        ]);

        $user->increment('saved_trips');

        return response()->json([
            'success' => true,
            'message' => 'Path saved successfully',
        ], 201);
    }

    /**
     * Unsave a path
     */
    public function unsave(Request $request, Path $path): JsonResponse
    {
        $user = $request->user();

        $savedPath = SavedPath::where('user_id', $user->id)
            ->where('path_id', $path->id)
            ->first();

        if (!$savedPath) {
            return response()->json([
                'success' => false,
                'message' => 'Path not saved',
            ], 404);
        }

        $savedPath->delete();
        $user->decrement('saved_trips');

        return response()->json([
            'success' => true,
            'message' => 'Path removed from saved',
        ]);
    }
}
