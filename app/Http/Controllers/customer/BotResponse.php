<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BotResponse extends Controller
{
    /**
     * Get consolidated user data using the stored procedure.
     */
    public function getConsolidatedUserData(Request $request)
    {
        try {
            // Get the authenticated user's ID from the token
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized access. Token is invalid.',
                ], 403);
            }

            $userId = $user->user_ID;

            // Call the stored procedure to get consolidated user data
            $consolidatedData = DB::select('CALL GetConsolidatedUserData(?)', [$userId]);

            if (empty($consolidatedData)) {
                return response()->json([
                    'message' => 'No data found for the user.',
                ], 404);
            }

            // Initialize grouped data structure
            $groupedData = [
                'user_ID' => $userId,
                'full_name' => $consolidatedData[0]->full_name,
                'email' => $consolidatedData[0]->email,
                'user_type' => $consolidatedData[0]->user_type,
                'forecasting_message' => $consolidatedData[0]->forecasting_message,
                'insights' => $consolidatedData[0]->insights,
                'saving_percentage' => $consolidatedData[0]->saving_percentage,
                'spending_percentage' => $consolidatedData[0]->spending_percentage,
                'thread_id' => $consolidatedData[0]->thread_id,
                'weekly_chart_data' => [],
                'monthly_chart_data' => [],
                'yearly_chart_data' => [],
                'forecast' => [
                    'monthly_expense' => $consolidatedData[0]->forecast_monthly_expense,
                    'total_expense' => $consolidatedData[0]->total_expense,
                    'total_income' => $consolidatedData[0]->total_income,
                    'weekly_expense' => $consolidatedData[0]->forecast_weekly_expense,
                    'yearly_expense' => $consolidatedData[0]->forecast_yearly_expense,
                ],
            ];

            // Deduplicate weekly, monthly, and yearly chart data
            $seenWeekly = [];
            $seenMonthly = [];
            $seenYearly = [];

            foreach ($consolidatedData as $row) {
                // Deduplicate weekly data
                if ($row->weekly_day_name && $row->weekly_expense) {
                    $key = $row->weekly_day_name;
                    if (!isset($seenWeekly[$key])) {
                        $groupedData['weekly_chart_data'][] = [
                            'day_name' => $row->weekly_day_name,
                            'expense' => $row->weekly_expense,
                        ];
                        $seenWeekly[$key] = true;
                    }
                }

                // Deduplicate monthly data
                if ($row->monthly_week_name && $row->chart_monthly_expense) {
                    $key = $row->monthly_week_name;
                    if (!isset($seenMonthly[$key])) {
                        $groupedData['monthly_chart_data'][] = [
                            'week_name' => $row->monthly_week_name,
                            'expense' => $row->chart_monthly_expense,
                        ];
                        $seenMonthly[$key] = true;
                    }
                }

                // Deduplicate yearly data
                if ($row->yearly_month_name && $row->yearly_chart_expense) {
                    $key = $row->yearly_month_name;
                    if (!isset($seenYearly[$key])) {
                        $groupedData['yearly_chart_data'][] = [
                            'month_name' => $row->yearly_month_name,
                            'expense' => $row->yearly_chart_expense,
                        ];
                        $seenYearly[$key] = true;
                    }
                }
            }

            return response()->json([
                'message' => 'Data retrieved successfully.',
                'data' => $groupedData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving user data: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while retrieving data.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getCurrentMonthExpenses(Request $request)
    {
        // Retrieve the authenticated user's ID
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized access.',
            ], 403);
        }

        $userId = $user->user_ID;

        try {
            // Get category-wise expenses
            $categoryExpenses = DB::select('CALL GetCategoryWiseExpense(?)', [$userId]);

            // Get amounts and dates of spendings
            $amountsAndDates = DB::select('CALL GetExpenseAmountsAndDates(?)', [$userId]);

            // Get spendings, savings, and savings percentage
            $spendingsAndSavings = DB::select('CALL GetUserSpendingsAndSavings(?)', [$userId]);

            // Structure the response
            return response()->json([
                'message' => 'Data retrieved successfully.',
                'category_expenses' => $categoryExpenses,
                'amounts_and_dates' => $amountsAndDates,
                'spendings_and_savings' => $spendingsAndSavings[0] ?? null,
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors
            Log::error('Error fetching current month expenses: ' . $e->getMessage());

            return response()->json([
                'error' => 'An error occurred while fetching data.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



}
