<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewReplyController extends Controller
{
    /**
     * Store a newly created reply.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:reviews,id',
            'comment' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the review exists
        $review = Review::findOrFail($request->review_id);

        // Create the reply
        $reply = ReviewReply::create([
            'user_id' => Auth::id(),
            'review_id' => $request->review_id,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply submitted successfully',
            'reply' => $reply
        ]);
    }

    /**
     * Display the specified reply.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $reply = ReviewReply::findOrFail($id);

        // Check if the user is authorized to view this reply
        if (Auth::id() !== $reply->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this reply'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    }

    /**
     * Update the specified reply.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $reply = ReviewReply::findOrFail($id);

        // Check if the user is authorized to update this reply
        if (Auth::id() !== $reply->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this reply'
            ], 403);
        }

        // Update the reply
        $reply->update([
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply updated successfully',
            'reply' => $reply
        ]);
    }

    /**
     * Remove the specified reply.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $reply = ReviewReply::findOrFail($id);

        // Check if the user is authorized to delete this reply
        if (Auth::id() !== $reply->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this reply'
            ], 403);
        }

        // Delete the reply
        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reply deleted successfully'
        ]);
    }
}
