<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Path;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Get user's reviews
     */
    public function index(Request $request): JsonResponse
    {
        $reviews = $request->user()->reviews()
            ->with(['path'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Create a new review
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'path_id' => 'required|exists:paths,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user has completed this path
        $hasCompletedPath = $request->user()->journeys()
            ->where('path_id', $request->path_id)
            ->where('status', 'completed')
            ->exists();

        if (!$hasCompletedPath) {
            return response()->json([
                'success' => false,
                'message' => 'You must complete this path before reviewing it',
            ], 403);
        }

        // Check if user already reviewed this path
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('path_id', $request->path_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this path',
            ], 400);
        }

        DB::transaction(function() use ($request) {
            // Create review
            $review = Review::create([
                'user_id' => $request->user()->id,
                'path_id' => $request->path_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Update path rating
            $this->updatePathRating($request->path_id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load('path'),
        ], 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function() use ($request, $review) {
            $review->update($request->only(['rating', 'comment']));
            $this->updatePathRating($review->path_id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->fresh(),
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        DB::transaction(function() use ($review) {
            $pathId = $review->path_id;
            $review->delete();
            $this->updatePathRating($pathId);
        });

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Update path rating based on reviews
     */
    protected function updatePathRating(int $pathId): void
    {
        $path = Path::find($pathId);

        $stats = Review::where('path_id', $pathId)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $path->update([
            'rating' => $stats->avg_rating ?? 0,
            'review_count' => $stats->count ?? 0,
        ]);
    }
}
