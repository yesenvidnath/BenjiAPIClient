<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetAllProfessinoalsByCategory extends Controller
{
    public function getAllProfessionalsByType($type = 'ALL')
    {
        $user = Auth::user();

        // Check for valid user type
        if (!$user || ($user->type !== 'Customer' && $user->type !== 'Admin')) {
            return response()->json([
                'error' => 'Unauthorized access. Only Admin or Customer users can access this resource.',
            ], 403);
        }

        try {
            // Call stored procedure
            $professionals = DB::select('CALL GetProfessionalsByType(?)', [$type]);

            // Check if professionals are found
            if (empty($professionals)) {
                return response()->json([
                    'message' => 'No professionals found for the specified type.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Professionals retrieved successfully.',
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

}
