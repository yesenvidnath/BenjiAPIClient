<?php

namespace App\Http\Controllers;

use App\Models\IncomeSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeManagementController extends Controller
{
    /**
     * Helper method to retrieve the authenticated user's ID.
     */
    protected function getAuthenticatedUserId()
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated and return the user_ID
        return $user ? $user->user_ID : null;
    }

    /**
     * Add a new income source for the authenticated user.
     */
    public function addIncomeSource(Request $request)
    {
        $request->validate([
            'source_name' => 'required|string',
            'amount' => 'required|numeric',
            'frequency' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $incomeSource = IncomeSource::create([
            'user_ID' => $userId,
            'source_name' => $request->source_name,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Income source added successfully', 'income_source' => $incomeSource], 201);
    }

    /**
     * Update an existing income source for the authenticated user.
     */
    public function updateIncomeSource(Request $request, $id)
    {
        $request->validate([
            'source_name' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'frequency' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $incomeSource = IncomeSource::where('user_ID', $userId)->where('income_source_ID', $id)->first();

        if (!$incomeSource) {
            return response()->json(['error' => 'Income source not found'], 404);
        }

        $incomeSource->update([
            'source_name' => $request->source_name ?? $incomeSource->source_name,
            'amount' => $request->amount ?? $incomeSource->amount,
            'frequency' => $request->frequency ?? $incomeSource->frequency,
            'description' => $request->description ?? $incomeSource->description,
        ]);

        return response()->json(['message' => 'Income source updated successfully', 'income_source' => $incomeSource], 200);
    }


    public function deleteIncomeSource($id)
    {
        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $incomeSource = IncomeSource::where('user_ID', $userId)->where('income_source_ID', $id)->first();

        if (!$incomeSource) {
            return response()->json(['error' => 'Income source not found'], 404);
        }

        $incomeSource->delete();

        return response()->json(['message' => 'Income source deleted successfully'], 200);
    }

    public function searchIncomeSources(Request $request)
    {
        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $query = IncomeSource::where('user_ID', $userId);

        if ($request->has('source_name')) {
            $query->where('source_name', 'LIKE', '%' . $request->source_name . '%');
        }

        if ($request->has('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $incomeSources = $query->get();

        return response()->json(['income_sources' => $incomeSources], 200);
    }


    public function getIncomeSource($id)
    {
        $userId = $this->getAuthenticatedUserId();

        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $incomeSource = IncomeSource::where('user_ID', $userId)->where('income_source_ID', $id)->first();

        if (!$incomeSource) {
            return response()->json(['error' => 'Income source not found'], 404);
        }

        return response()->json(['income_source' => $incomeSource], 200);
    }

}
