<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ground;
use Illuminate\Http\Request;

class GroundController extends Controller
{
    /**
     * Display all grounds with optional filtering
     */
    public function allGrounds(Request $request)
    {
        // Get filtering parameters
        $category = $request->query('category', 'allgrounds');
        $search = $request->query('search');
        $price = $request->query('price');

        $query = Ground::where('status', 'active');

        // Apply category filter if not 'allgrounds'
        if ($category !== 'allgrounds') {
            $query->where('ground_category', $category);
        }

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        // Apply price filter if provided
        if ($price) {
            $priceRange = explode('-', $price);
            if (count($priceRange) === 2) {
                $minPrice = $priceRange[0];
                $maxPrice = $priceRange[1];
                $query->whereBetween('price_per_hour', [$minPrice, $maxPrice]);
            } elseif ($price === '100+') {
                $query->where('price_per_hour', '>=', 100);
            }
        }

        $grounds = $query->with('images')->orderBy('created_at', 'desc')->get();

        return view('user.all-grounds', compact('grounds', 'category', 'search', 'price'));
    }

    /**
     * Display a specific ground's details
     */
    public function viewGround($id)
    {
        $ground = Ground::with(['images', 'slots', 'features'])->findOrFail($id);

        // You can add additional logic here to fetch related data

        return view('user.view-ground-details', compact('ground'));
    }
}
