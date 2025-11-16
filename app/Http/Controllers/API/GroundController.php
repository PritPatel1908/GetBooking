<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ground;

class GroundController extends Controller
{
    /**
     * Filter grounds based on category, search, and price
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterGrounds(Request $request)
    {
        $query = Ground::where('status', 'active');

        // Filter by category
        if ($request->has('category') && $request->category !== 'allgrounds') {
            $query->where('ground_category', $request->category);
        }

        // Filter by search term
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by price range
        if ($request->has('price') && !empty($request->price)) {
            $priceRange = explode('-', $request->price);
            // Note: Price filtering now needs to be done through ground_slots table
            // This would require a more complex query with joins
            // For now, we'll skip price filtering or implement it differently
        }

        // Get grounds with their relationships
        $grounds = $query->with(['images'])->get();

        // Format grounds for response
        $formattedGrounds = $grounds->map(function ($ground) {
            return [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->getImageUrl(),
                'images' => $ground->images->map(function ($image) {
                    return asset($image->image_path);
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'grounds' => $formattedGrounds,
            'count' => $formattedGrounds->count()
        ]);
    }
}
