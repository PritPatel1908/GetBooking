<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Store contact message
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create contact message
            $contactMessage = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'new'
            ]);

            // Send email notification to admin (optional)
            // Mail::to('admin@getbooking.com')->send(new ContactMessageNotification($contactMessage));

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully! We will get back to you soon.',
                'data' => $contactMessage
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all contact messages for admin
     */
    public function index()
    {
        // Return React app for SPA
        return view('admin.react-app');
    }

    /**
     * Show specific contact message
     */
    public function show($id)
    {
        // Return React app for SPA - detail pages will be handled by React Router
        return view('admin.react-app');
    }

    /**
     * Update message status
     */
    public function updateStatus(Request $request, $id)
    {
        $message = ContactMessage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,read,replied',
            'admin_reply' => 'nullable|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = ['status' => $request->status];

        if ($request->status === 'replied' && $request->admin_reply) {
            $updateData['admin_reply'] = $request->admin_reply;
            $updateData['replied_at'] = now();
        }

        $message->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Message status updated successfully',
            'data' => $message
        ], 200);
    }

    /**
     * Delete contact message
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ], 200);
    }
}
