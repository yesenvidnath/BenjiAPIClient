<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        try {
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

            // Send data to the external endpoint and process the response
            $botData = $this->sendExpensesToBotEndpoint($organizedData);

            // Call storeBotDataTest with the received bot data
            return $this->storeBotDataTest($botData);

        } catch (\Exception $e) {
            Log::error('Error in getAllExpenses: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send expense data to an external endpoint and process the response data.
     *
     * @param array $organizedData
     * @return array
     * @throws \Exception
     */
    private function sendExpensesToBotEndpoint(array $organizedData)
    {
        $botEndpoint = env('BOT_API_END_POINT_POST_DATA');

        if (!$botEndpoint) {
            throw new \Exception('BOT_API_END_POINT_POST_DATA is not set in the .env file.');
        }

        try {
            $jsonData = json_encode(['data' => $organizedData]);

            // Initialize cURL
            $ch = curl_init($botEndpoint);

            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false // Only for testing
            ]);

            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL error: ' . curl_error($ch));
            }

            curl_close($ch);

            // Process response
            if ($httpCode >= 200 && $httpCode < 300) {
                $botData = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $botData;
                } else {
                    throw new \Exception('Invalid JSON response from bot endpoint');
                }
            } else {
                throw new \Exception("HTTP error: $httpCode - $response");
            }
        } catch (\Exception $e) {
            Log::error('Error sending data to bot endpoint: ' . $e->getMessage());
            throw new \Exception('An error occurred while sending data to the bot endpoint: ' . $e->getMessage());
        }
    }

    /**
     * Store bot data in the database.
     *
     * @param array $botData
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBotDataTest(array $botData)
    {
        try {
            foreach ($botData['data'] as $entry) {
                // Prepare JSON strings for the stored procedure
                $weeklyData = json_encode(
                    array_map(function ($x, $y) {
                        return ['day_name' => $x, 'expense' => $y];
                    }, $entry['chart_data']['weekly']['x'], $entry['chart_data']['weekly']['y'])
                );

                $monthlyData = json_encode(
                    array_map(function ($x, $y) {
                        return ['week_name' => $x, 'expense' => $y];
                    }, $entry['chart_data']['monthly']['x'], $entry['chart_data']['monthly']['y'])
                );

                $yearlyData = json_encode(
                    array_map(function ($x, $y) {
                        return ['month_name' => $x, 'expense' => $y];
                    }, $entry['chart_data']['yearly']['x'], $entry['chart_data']['yearly']['y'])
                );

                $forecast = json_encode($entry['forecast']);
                $insights = json_encode([
                    'forecasting_message' => $entry['forecasting_message'],
                    'insights' => $entry['insights'],
                    'saving_percentage' => $entry['saving_percentage'],
                    'spending_percentage' => $entry['spending_percentage'],
                ]);

                // Call the stored procedure
                DB::statement('CALL AddUserBotInfo(?, ?, ?, ?, ?, ?, ?)', [
                    $entry['user_id'],
                    $weeklyData,
                    $monthlyData,
                    $yearlyData,
                    $forecast,
                    $insights,
                    $entry['thread_id'],
                ]);
            }

            return response()->json([
                'message' => 'Bot data stored successfully',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in storeBotDataTest: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to store bot data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
