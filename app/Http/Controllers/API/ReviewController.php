<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ground;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Get reviews for a ground
     *
     * @param int $groundId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroundReviews($groundId)
    {
        try {
            $ground = Ground::findOrFail($groundId);

            $reviews = Review::with(['user:id,name,email'])
                ->where('ground_id', $groundId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    // Format the review data
                    return [
                        'id' => $review->id,
                        'user_id' => $review->user_id,
                        'ground_id' => $review->ground_id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                        'user' => [
                            'id' => $review->user->id ?? null,
                            'name' => $review->user->name ?? 'Anonymous'
                        ]
                    ];
                });

            // Get authenticated user's review if available
            $userReview = null;
            if (Auth::check()) {
                $userReview = Review::where('ground_id', $groundId)
                    ->where('user_id', Auth::id())
                    ->first();
            }

            return response()->json([
                'success' => true,
                'reviews' => $reviews,
                'count' => $reviews->count(),
                'userReview' => $userReview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reviews: ' . $e->getMessage()
            ], 500);
        }
    }
}
