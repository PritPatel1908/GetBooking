<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Ground;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a newly created review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'ground_id' => 'required|exists:grounds,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user has already reviewed this ground
        $existingReview = Review::where('user_id', Auth::id())
            ->where('ground_id', $request->ground_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this ground'
            ], 422);
        }

        // Create the review
        $review = Review::create([
            'user_id' => Auth::id(),
            'ground_id' => $request->ground_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'review' => $review
        ]);
    }

    /**
     * Display the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $review = Review::findOrFail($id);

        // Check if the user is authorized to view this review
        if (Auth::id() !== $review->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this review'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'review' => $review
        ]);
    }

    /**
     * Update the specified review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::findOrFail($id);

        // Check if the user is authorized to update this review
        if (Auth::id() !== $review->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this review'
            ], 403);
        }

        // Update the review
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'review' => $review
        ]);
    }

    /**
     * Remove the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Check if the user is authorized to delete this review
        if (Auth::id() !== $review->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this review'
            ], 403);
        }

        // Delete the review (this will also delete all associated replies due to the cascade)
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}
