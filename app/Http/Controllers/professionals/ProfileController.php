<?php

namespace App\Http\Controllers\Professionals;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Convert customer to professional
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convertToProfessional(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'professionalType' => [
                    'required',
                    Rule::in([
                        'Accountant', 'Financial Advisor', 'Stock Broker', 'Banker',
                        'Insurance Agent', 'Investment Specialist', 'Tax Consultant',
                        'Real Estate Agent', 'Loan Officer', 'Wealth Manager',
                        'Mortgage Advisor', 'Retirement Planner', 'Business Consultant', 'Other'
                    ])
                ],
                'certificates' => 'required|array',
                'certificates.*.certificateName' => 'required|string|max:255',
                'certificates.*.certificateDate' => 'required|date',
                'certificates.*.certificateImage' => 'required|file|mimes:jpg,jpeg,png|max:1024'
            ]);

            $userID = Auth::id(); // Get the authenticated user ID
            $certificatesData = [];

            foreach ($validatedData['certificates'] as $index => $certificateDetails) {
                $certificateFile = $request->file("certificates.{$index}.certificateImage");

                // Save the file
                $path = $certificateFile->store('certificates', 'public');

                // Collect certificate details
                $certificatesData[] = [
                    'certificateID' => Str::uuid()->toString(),
                    'certificateName' => $certificateDetails['certificateName'],
                    'certificateDate' => $certificateDetails['certificateDate'],
                    'certificateImage' => $path,
                ];
            }

            DB::statement('CALL ConvertCustomerToProfessional(?, ?, ?)', [
                $userID,
                json_encode($certificatesData),
                $validatedData['professionalType']
            ]);

            return response()->json([
                'message' => 'Successfully converted to professional',
                'certificates' => $certificatesData
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error converting user: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to convert user', 'error' => $e->getMessage()], 500);
        }


        // Get authenticated user ID
        $userID = $this->getAuthenticatedUserId();
        if (!$userID) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Start database transaction
        DB::beginTransaction();
        try {
            // Prepare certificates data with uploaded image paths
            $certificatesData = [];

            // Debug: Print out the entire request files
            Log::info('Request Files:', ['files' => $request->allFiles()]);

            // Iterate through certificates
            foreach ($validatedData['certificates'] as $index => $certificateDetails) {
                // Get the file for this certificate
                $certificateFile = $request->file("certificates.{$index}.certificateImage");

                // Generate encrypted filename
                $encryptedFileName = $this->uploadCertificateImage($certificateFile);

                // Prepare certificate data
                $certificatesData[] = [
                    'certificateID' => Str::uuid()->toString(), // Generate unique ID
                    'certificateName' => $certificateDetails['certificateName'],
                    'certificateDate' => $certificateDetails['certificateDate'],
                    'certificateImage' => $encryptedFileName
                ];
            }

            // Call stored procedure
            DB::statement('CALL ConvertCustomerToProfessional(?, ?, ?)', [
                $userID,
                json_encode($certificatesData),
                $validatedData['professionalType']
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'message' => 'Successfully converted to professional',
                'certificates' => $certificatesData
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log the error
            Log::error('Professional conversion error: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to convert to professional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload certificate image
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */

    private function uploadCertificateImage($file)
    {
        // Validate file
        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Generate an encrypted filename
        $originalExtension = $file->getClientOriginalExtension();
        $encryptedFileName = Str::random(40) . '.' . $originalExtension;

        // Store the file in the certificates directory
        $path = $file->storeAs('certificates', $encryptedFileName, 'public');

        return $encryptedFileName;
    }

    /**
     * Helper method to retrieve the authenticated user's ID
     *
     * @return int|null
     */
    private function getAuthenticatedUserId()
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Return the user_ID if authenticated, else null
        return $user ? $user->user_ID : null;
    }



    /**
     * Update Professional Status and Type
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */


    public function updateProfessionalProfile(Request $request)
    {
        // Get the authenticated user
        $authenticatedUser = Auth::user();

        // Check if the authenticated user is an admin
        $isAdmin = DB::table('admins')
            ->where('user_ID', $authenticatedUser->user_ID)
            ->exists();

        // If not an admin, deny access
        if (!$isAdmin) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can update professional profiles.'
            ], 403);
        }

        // Validate the incoming request
        try {
            $validatedData = $request->validate([
                'user_ID' => 'required|exists:users,user_ID',
                'status' => 'sometimes|in:pending,active,banned,suspended',
                'type' => 'sometimes|in:Accountant,Financial Advisor,Stock Broker,Banker,Insurance Agent,Investment Specialist,Tax Consultant,Real Estate Agent,Loan Officer,Wealth Manager,Mortgage Advisor,Retirement Planner,Business Consultant,Other'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Start database transaction
        DB::beginTransaction();
        try {
            // Prepare the update query
            $updateData = [];
            $bindings = [];

            // Check if status is being updated
            if (isset($validatedData['status'])) {
                $updateData[] = 'status = ?';
                $bindings[] = $validatedData['status'];
            }

            // Check if type is being updated
            if (isset($validatedData['type'])) {
                $updateData[] = 'type = ?';
                $bindings[] = $validatedData['type'];
            }

            // If no updates are specified, return error
            if (empty($updateData)) {
                return response()->json([
                    'message' => 'No update fields provided'
                ], 400);
            }

            // Add user_ID to bindings
            $bindings[] = $validatedData['user_ID'];

            // Construct and execute the update query
            $query = "UPDATE professionals SET " . implode(', ', $updateData) .
                    ", updated_at = NOW() " . // Always update timestamp
                    "WHERE user_ID = ?";

            $affected = DB::update($query, $bindings);

            // Commit transaction
            DB::commit();

            // Check if any rows were updated
            if ($affected === 0) {
                return response()->json([
                    'message' => 'No professional record found for the given user ID',
                ], 404);
            }

            // Log the admin action
            Log::info('Professional profile updated', [
                'updated_by_admin' => $authenticatedUser->user_ID,
                'target_user_ID' => $validatedData['user_ID'],
                'updated_fields' => array_keys($validatedData)
            ]);

            return response()->json([
                'message' => 'Professional profile updated successfully',
                'user_ID' => $validatedData['user_ID'],
                'updated_fields' => array_keys($validatedData)
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log the error
            Log::error('Professional profile update error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update professional profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Function to call the GetAllProfessionals stored procedure
    public function getAllProfessionals()
    {
        // Get authenticated user ID
        $userID = $this->getAuthenticatedUserId();
        if (!$userID) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            // Call the stored procedure
            $results = DB::select('CALL GetAllProfessionals()');

            // Structure the data
            $professionals = [];
            foreach ($results as $row) {
                $userID = $row->user_ID;

                // Initialize professional if not already set
                if (!isset($professionals[$userID])) {
                    $professionals[$userID] = [
                        'user_id' => $userID,
                        'full_name' => $row->full_name,
                        'address' => $row->address,
                        'type' => $row->type,
                        'dob' => $row->DOB,
                        'phone_number' => $row->phone_number,
                        'email' => $row->email,
                        'profile_image' => $row->profile_image,
                        'bank_choice' => $row->bank_choice,
                        'status' => $row->status,
                        'professional_type' => $row->professional_type,
                        'charge_per_hour' => $row->charge_per_Hr,
                        'certificates' => [],
                    ];
                }

                // Add certificate information if available
                if ($row->certificate_ID) {
                    $professionals[$userID]['certificates'][] = [
                        'certificate_id' => $row->certificate_ID,
                        'certificate_name' => $row->certificate_name,
                        'certificate_date' => $row->certificate_date,
                        'certificate_image' => $row->certificate_image,
                    ];
                }
            }

            // Reset array keys for a clean JSON response
            $structuredData = array_values($professionals);

            return response()->json(['data' => $structuredData], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching professionals', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateProfessionalDetails(Request $request, $professionalId)
    {
        // Get authenticated user ID
        $userID = $this->getAuthenticatedUserId();
        if (!$userID) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validatedData = $request->validate([
            'status' => 'required|in:pending,active,banned,suspended',
            'charge_per_hr' => 'required|numeric|min:0',
        ]);

        try {
            // Update the professional's status and charge per hour in the database
            $affectedRows = DB::table('professionals')
                ->where('user_ID', $professionalId)
                ->update([
                    'status' => $validatedData['status'],
                    'charge_per_Hr' => $validatedData['charge_per_hr'],
                ]);

            if ($affectedRows === 0) {
                return response()->json(['message' => 'Professional not found or no changes made'], 404);
            }

            return response()->json(['message' => 'Details updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating details', 'error' => $e->getMessage()], 500);
        }
    }

}
