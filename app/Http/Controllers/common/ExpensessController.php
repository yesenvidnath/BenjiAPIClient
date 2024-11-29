<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpensessController extends Controller
{

    /**
     * Add one or more expenses (only accessible by admins).
    */
    public function addExpenses(Request $request)
    {
        // Retrieve the authenticated user's ID
        $authenticatedUserID = $this->getAuthenticatedUserId();

        // Check if the user is an admin
        $isAdmin = DB::table('admins')->where('user_ID', $authenticatedUserID)->exists();

        // If the user is not an admin, deny access
        if (!$isAdmin) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can add expenses.'
            ], 403);
        }

        // Validate the input data: Expecting an array of expenses
        $validated = $request->validate([
            'expenses' => 'required|array', // Expecting an array of expenses
            'expenses.*.reason_ID' => 'required|exists:reasons,reason_ID', // Ensure each reason exists
            'expenses.*.amount' => 'required|numeric',
            'expenses.*.comment' => 'nullable|string',
            'expenses.*.description' => 'nullable|string|max:255',
        ]);

        try {
            // Loop through the expenses array and call the stored procedure for each
            foreach ($validated['expenses'] as $expense) {
                DB::statement('
                    CALL AddExpense(?, ?, ?, ?, ?)
                ', [
                    $authenticatedUserID,            // Authenticated user ID
                    $expense['reason_ID'],           // Reason ID
                    $expense['amount'],              // Amount
                    $expense['comment'] ?? null,     // Comment (nullable)
                    $expense['description'] ?? null  // Description (nullable)
                ]);
            }

            return response()->json(['message' => 'Expenses added successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

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
}
