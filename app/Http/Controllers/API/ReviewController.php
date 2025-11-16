<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ground;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

            // Get reviews with relationships
            $reviewsQuery = Review::with(['user:id,name,email', 'replies.user:id,name,email'])
                ->where('ground_id', $groundId);

            // Calculate average rating before mapping (on actual models)
            $reviewsCollection = $reviewsQuery->get();
            $averageRating = $reviewsCollection->count() > 0
                ? round($reviewsCollection->avg('rating'), 1)
                : 0;

            // Get authenticated user's review ID if available
            $currentUserId = Auth::check() ? Auth::id() : null;

            // Sort reviews: User's own review first, then others by date (newest first)
            if ($currentUserId) {
                $reviewsCollection = $reviewsCollection->sort(function ($a, $b) use ($currentUserId) {
                    $aIsUserReview = $a->user_id == $currentUserId;
                    $bIsUserReview = $b->user_id == $currentUserId;

                    // If one is user's review and other is not, prioritize user's review
                    if ($aIsUserReview && !$bIsUserReview) {
                        return -1; // $a (user's review) comes first
                    }
                    if (!$aIsUserReview && $bIsUserReview) {
                        return 1; // $b (user's review) comes first
                    }
                    // Both are user's reviews or neither are, sort by date (newest first)
                    return strtotime($b->created_at) - strtotime($a->created_at);
                })->values(); // Re-index the collection
            } else {
                // If user is not logged in, just sort by date (newest first)
                $reviewsCollection = $reviewsCollection->sortByDesc('created_at')->values();
            }

            // Map reviews for response
            $reviews = $reviewsCollection->map(function ($review) {
                // Format the review data
                return [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'ground_id' => $review->ground_id,
                    'rating' => (int)$review->rating, // Ensure rating is integer
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                    'user' => [
                        'id' => $review->user->id ?? null,
                        'name' => $review->user->name ?? 'Anonymous'
                    ],
                    'replies' => $review->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'user_id' => $reply->user_id,
                            'review_id' => $reply->review_id,
                            'comment' => $reply->comment,
                            'created_at' => $reply->created_at,
                            'updated_at' => $reply->updated_at,
                            'user' => [
                                'id' => $reply->user->id ?? null,
                                'name' => $reply->user->name ?? 'Anonymous'
                            ]
                        ];
                    })
                ];
            });

            // Get authenticated user's review if available
            $userReview = null;
            if (Auth::check()) {
                $userReviewData = Review::with(['user:id,name,email'])
                    ->where('ground_id', $groundId)
                    ->where('user_id', Auth::id())
                    ->first();

                if ($userReviewData) {
                    $userReview = [
                        'id' => $userReviewData->id,
                        'user_id' => $userReviewData->user_id,
                        'ground_id' => $userReviewData->ground_id,
                        'rating' => $userReviewData->rating,
                        'comment' => $userReviewData->comment,
                        'created_at' => $userReviewData->created_at,
                        'updated_at' => $userReviewData->updated_at,
                        'user' => [
                            'id' => $userReviewData->user->id ?? null,
                            'name' => $userReviewData->user->name ?? 'Anonymous'
                        ]
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'reviews' => $reviews,
                'count' => $reviews->count(),
                'userReview' => $userReview,
                'average_rating' => $averageRating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new review for a ground
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'ground_id' => 'required|exists:grounds,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user already has a review for this ground
            $existingReview = Review::where('user_id', Auth::id())
                ->where('ground_id', $request->ground_id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this ground. Please update your existing review.'
                ], 409);
            }

            // Verify ground exists
            $ground = Ground::findOrFail($request->ground_id);

            // Create new review
            $review = Review::create([
                'user_id' => Auth::id(),
                'ground_id' => $request->ground_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Load user relationship
            $review->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!',
                'review' => [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'ground_id' => $review->ground_id,
                    'rating' => (int)$review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                    'user' => [
                        'id' => $review->user->id ?? null,
                        'name' => $review->user->name ?? 'Anonymous'
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing review
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $review = Review::findOrFail($id);

            // Check if the review belongs to the authenticated user
            if ($review->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this review.'
                ], 403);
            }

            // Update the review
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->save();

            // Load user relationship
            $review->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully!',
                'review' => [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'ground_id' => $review->ground_id,
                    'rating' => (int)$review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                    'user' => [
                        'id' => $review->user->id ?? null,
                        'name' => $review->user->name ?? 'Anonymous'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            // Check if the review belongs to the authenticated user
            if ($review->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this review.'
                ], 403);
            }

            // Delete the review (this will also delete all associated replies due to cascade)
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single review by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $review = Review::with(['user:id,name,email', 'replies.user:id,name,email'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'review' => [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'ground_id' => $review->ground_id,
                    'rating' => (int)$review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                    'user' => [
                        'id' => $review->user->id ?? null,
                        'name' => $review->user->name ?? 'Anonymous'
                    ],
                    'replies' => $review->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'user_id' => $reply->user_id,
                            'review_id' => $reply->review_id,
                            'comment' => $reply->comment,
                            'created_at' => $reply->created_at,
                            'updated_at' => $reply->updated_at,
                            'user' => [
                                'id' => $reply->user->id ?? null,
                                'name' => $reply->user->name ?? 'Anonymous'
                            ]
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching review: ' . $e->getMessage()
            ], 500);
        }
    }
}
