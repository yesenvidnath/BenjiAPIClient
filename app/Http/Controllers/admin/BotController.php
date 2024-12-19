<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BotController extends Controller
{
   /**
     * Fetch all user expense and income data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllExpenses(Request $request)
    {
        // Check if the authenticated user is an Admin
        $user = Auth::user();

        if (!$user || $user->type !== 'Admin') {
            return response()->json([
                'error' => 'Unauthorized access. Only Admins can access this resource.',
            ], 403);
        }

        // Query the UserExpenseIncomeView
        $data = DB::table('UserExpenseIncomeView')->get();

        // Check if data is found
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No data found.',
            ], 404);
        }

        // Organize data into a structured format
        $organizedData = [];
        foreach ($data as $row) {
            $userId = $row->user_ID;

            // Initialize user data structure if not already set
            if (!isset($organizedData[$userId])) {
                $organizedData[$userId] = [
                    'user_ID' => $userId,
                    'user_name' => $row->user_name,
                    'user_email' => $row->user_email,
                    'incomes' => [],
                    'expenses' => [],
                ];
            }

            // Add income data
            if (!is_null($row->income_source_name)) {
                $organizedData[$userId]['incomes'][] = [
                    'source_name' => $row->income_source_name,
                    'amount' => $row->income_amount,
                    'frequency' => $row->income_frequency,
                    'description' => $row->income_description,
                ];
            }

            // Add expense data
            if (!is_null($row->expense_id)) {
                $organizedData[$userId]['expenses'][] = [
                    'expense_id' => $row->expense_id,
                    'expense_date' => $row->expense_date,
                    'reason_id' => $row->expense_reason_id,
                    'amount' => $row->expense_amount,
                    'description' => $row->expense_description,
                    'reason_text' => $row->reason_text,
                    'category_name' => $row->category_name,
                ];
            }
        }

        // Reset array keys to be sequential
        $organizedData = array_values($organizedData);

        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data' => $organizedData,
        ], 200);
    }
}
