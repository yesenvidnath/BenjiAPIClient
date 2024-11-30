<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpensessController extends Controller
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
     * Add expense along with multiple expense list items.
     */
    public function addExpense(Request $request)
    {
        // Validate input data
        $request->validate([
            'expensesDetails' => 'required|array',    // Array of expense details
            'expensesDetails.*.reason_ID' => 'required|integer',
            'expensesDetails.*.amount' => 'required|numeric',
            'expensesDetails.*.description' => 'nullable|string|max:255',
        ]);

        // Get the authenticated user's ID
        $userID = $this->getAuthenticatedUserId();

        // If no user is authenticated, return an error
        if (!$userID) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Prepare the expenses details to be passed to the stored procedure
        $expensesDetails = $request->input('expensesDetails'); // Array of expenses

        try {
            // Begin the transaction
            DB::beginTransaction();

            // Call the stored procedure to add the expense record and related expense list items
            DB::statement('CALL AddExpense(?, ?)', [
                $userID,
                json_encode($expensesDetails) // Encode the expenses array as JSON for the stored procedure
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json(['message' => 'Expense added successfully'], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Return error response
            return response()->json(['error' => 'Failed to add expense: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Update existing expense and its related expense list items.
     */
    public function updateExpense(Request $request, $expenseID)
    {
        // Validate input data
        $request->validate([
            'expensesDetails' => 'required|array',    // Array of expense details
            'expensesDetails.*.reason_ID' => 'required|integer',
            'expensesDetails.*.amount' => 'required|numeric',
            'expensesDetails.*.description' => 'nullable|string|max:255',
        ]);

        // Get the authenticated user's ID
        $userID = $this->getAuthenticatedUserId();

        // If no user is authenticated, return an error
        if (!$userID) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Check if the expense exists and belongs to the authenticated user
        $expense = DB::table('expenses')
            ->where('expenses_ID', $expenseID)
            ->where('user_ID', $userID)
            ->first();

        if (!$expense) {
            return response()->json(['error' => 'Expense not found or user not authorized'], 404);
        }

        // Prepare the expenses details to be passed to the stored procedure
        $expensesDetails = $request->input('expensesDetails'); // Array of expenses

        try {
            // Begin the transaction
            DB::beginTransaction();

            // Call the stored procedure to update the expense record and related expense list items
            DB::statement('CALL UpdateExpense(?, ?, ?)', [
                $expenseID,
                $userID,
                json_encode($expensesDetails) // Encode the expenses array as JSON for the stored procedure
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json(['message' => 'Expense updated successfully'], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Return error response
            return response()->json(['error' => 'Failed to update expense: ' . $e->getMessage()], 500);
        }
    }


    public function deleteExpenseItem(Request $request, $expenseID, $expenseListItemID)
    {
        // Step 1: Validate if the expense record exists for the given expenseID
        $expense = DB::table('expenses')->where('expenses_ID', $expenseID)->first();

        if (!$expense) {
            return response()->json(['message' => 'Expense record not found.'], 404);
        }

        // Step 2: Validate if the expense item exists in the expenses_list for the given expenseID and expenseslist_ID
        $expenseListItem = DB::table('expenses_list')->where('expenses_id', $expenseID)->where('expenseslist_ID', $expenseListItemID)->first();

        if (!$expenseListItem) {
            return response()->json(['message' => 'Expense list item not found.'], 404);
        }

        // Step 3: Delete the specific record from the expenses_list table
        DB::table('expenses_list')->where('expenseslist_ID', $expenseListItemID)->delete();

        // Step 4: Check if there are any remaining records in expenses_list for this expenseID
        $remainingItems = DB::table('expenses_list')->where('expenses_id', $expenseID)->count();

        // Step 5: If no items remain, delete the corresponding record from the expenses table
        if ($remainingItems == 0) {
            DB::table('expenses')->where('expenses_ID', $expenseID)->delete();
        }

        return response()->json(['message' => 'Expense list item deleted successfully.'], 200);
    }


    public function getAllExpenses(Request $request)
    {
        // Step 1: Get the authenticated user's ID
        $userID = $this->getAuthenticatedUserId(); // Use the helper method

        if (!$userID) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Step 2: Retrieve expenses for the authenticated user
        $expenses = DB::table('expenses')
            ->where('user_ID', $userID) // Filter by the authenticated user's ID
            ->get(['expenses_ID', 'created_at', 'updated_at']); // Exclude user_ID here

        // Step 3: Retrieve the associated expense list items for each expense
        $expensesWithDetails = [];

        foreach ($expenses as $expense) {
            // Fetch the expense list items related to this expense
            $expenseItems = DB::table('expenses_list')
                ->where('expenses_id', $expense->expenses_ID) // Link to the expense ID
                ->get(['expenseslist_ID', 'reason_ID', 'amount', 'description', 'created_at', 'updated_at']); // Specify the fields to retrieve

            // Add expense and its list items to the result array
            $expensesWithDetails[] = [
                'expense' => $expense,  // The main expense record
                'expense_items' => $expenseItems  // The associated list items
            ];
        }

        // Step 4: Return the combined result as a response
        return response()->json(['data' => $expensesWithDetails], 200);
    }


    /**
     * Get all expenses data for the authenticated user.
     */
    public function getUserExpensesInfo(Request $request)
    {
        // Get the authenticated user's ID using your helper method
        $userID = $this->getAuthenticatedUserId();

        // If the user ID is not found, return a response
        if (!$userID) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Call the stored procedure to get the expenses data
        $expensesData = DB::select('CALL GetUserExpensesInfo(?)', [$userID]);

        // Return the data with user_ID as the parent (without including user_ID in the expenses data)
        return response()->json([
            'user_ID' => $userID,
            'expenses' => $expensesData
        ], 200);
    }


}
