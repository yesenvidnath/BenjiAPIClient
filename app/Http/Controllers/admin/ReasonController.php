<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reason;
use App\Models\Category;

class ReasonController extends Controller
{
    /**
     * Store reasons under specific categories
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data (you can add more validation if needed)
        $request->validate([
            'reasons' => 'required|array',
            'reasons.*.reason' => 'required|string|max:255',
            'reasons.*.category_ID' => 'required|integer|exists:categories,category_ID',  // Ensure valid category ID
        ]);

        // Prepare the reasons array for insertion
        $reasonsToInsert = [];

        foreach ($request->reasons as $reason) {
            $reasonsToInsert[] = [
                'reason' => $reason['reason'],
                'category_ID' => $reason['category_ID'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Begin a transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Insert all the reasons into the `reasons` table
            Reason::insert($reasonsToInsert);

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Reasons added successfully.'], 200);
        } catch (\Exception $e) {
            // Rollback if something goes wrong
            DB::rollback();

            return response()->json(['error' => 'Failed to add reasons: ' . $e->getMessage()], 500);
        }
    }

    public function getAllReasons()
    {
        // Retrieve all reasons with their related category
        $reasons = Reason::with('category')->get(); // Eloquent relationship to fetch category details

        // Return the response as a JSON array
        return response()->json($reasons, 200);
    }

}
