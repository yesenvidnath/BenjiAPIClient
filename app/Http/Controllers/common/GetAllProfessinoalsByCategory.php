<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetAllProfessinoalsByCategory extends Controller
{
    public function getAllProfessionals(Request $request)
    {
        $user = Auth::user();

        // Validate user type (only Admin or Customer allowed)
        if (!$user || ($user->type !== 'Customer' && $user->type !== 'Admin')) {
            return response()->json([
                'error' => 'Unauthorized access. Only Admin or Customer users can access this resource.',
            ], 403);
        }

        try {
            // Always fetch all professionals
            $professionals = DB::select('CALL GetProfessionalsByType(?)', ['ALL']);

            // Check if professionals are found
            if (empty($professionals)) {
                return response()->json([
                    'message' => 'No professionals found.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'All professionals retrieved successfully.',
                'data' => $professionals,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching professionals: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while retrieving professionals.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function getAllProfessionalTypes(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::user();

        // Validate user type (only Admin or Customer allowed)
        if (!$user || ($user->type !== 'Customer' && $user->type !== 'Admin')) {
            return response()->json([
                'error' => 'Unauthorized access. Only Admin or Customer users can access this resource.',
            ], 403);
        }

        try {
            // Fetch ENUM values for the 'type' column in the 'professionals' table
            $enumValues = DB::select("
                SELECT COLUMN_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = 'professionals'
                  AND COLUMN_NAME = 'type'
                  AND TABLE_SCHEMA = DATABASE()
            ");

            // Parse the ENUM values
            if (!empty($enumValues)) {
                $enumValues = $enumValues[0]->COLUMN_TYPE;
                preg_match("/^enum\((.*)\)$/", $enumValues, $matches);
                $types = array_map(function ($value) {
                    return trim($value, "'");
                }, explode(",", $matches[1]));

                return response()->json([
                    'message' => 'Professional types retrieved successfully.',
                    'data' => $types,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No professional types found.',
                    'data' => [],
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching professional types: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while retrieving professional types.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
