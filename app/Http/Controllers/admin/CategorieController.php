<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorieController extends Controller
{
    // Store multiple categories
    public function store(Request $request)
    {
        // Validate the incoming data (array of categories)
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.category' => 'required|string|unique:categories,category',  // Ensure unique categories
        ]);

        // Get the categories array
        $categories = $validated['categories'];

        // Insert categories into the database
        foreach ($categories as $category) {
            Category::create([
                'category' => $category['category'],
            ]);
        }

        // Return a success message
        return response()->json(['message' => 'Categories added successfully'], 201);
    }
}
