<?php

namespace App\Http\Controllers\professionals;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Professional;
use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    // Helper method to retrieve the authenticated user's ID
    public function getAuthenticatedUserId()
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Return the user_ID if authenticated, else null
        return $user ? $user->user_ID : null;
    }

    // Function to convert customer to professional
    public function convertCustomerToProfessional(Request $request)
    {
        // Get the authenticated user's ID using the helper method
        $userId = $this->getAuthenticatedUserId();

        // If no authenticated user is found, return an error response
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'certificate_details' => 'required|array',
            'certificate_details.*.certificate_name' => 'required|string',
            'certificate_details.*.certificate_date' => 'required|date',
            'certificate_details.*.certificate_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'professional_type' => 'required|in:Accountant,Financial Advisor,Stock Broker,Banker,Insurance Agent,Investment Specialist,Tax Consultant,Real Estate Agent,Loan Officer,Wealth Manager,Mortgage Advisor,Retirement Planner,Business Consultant,Other',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $certificateDetails = $request->input('certificate_details');
        $professionalType = $request->input('professional_type');

        // Step 1: Find the user
        $user = User::findOrFail($userId);

        // Step 2: Create the professional record
        $professional = Professional::create([
            'user_ID' => $user->user_ID,
            'status' => 'pending',
            'type' => $professionalType,
        ]);

        // Step 3: Handle the certificate details
        foreach ($certificateDetails as $certificate) {
            $certificateName = $certificate['certificate_name'];
            $certificateDate = $certificate['certificate_date'];
            $certificateImage = $certificate['certificate_image'];

            // If there is a certificate image, handle the file upload
            if ($certificateImage) {
                // Check if the certificate image is a URL or a file
                if (filter_var($certificateImage, FILTER_VALIDATE_URL)) {
                    $certificateImagePath = $certificateImage; // If URL, use the URL as is
                } else {
                    // If it's a file, upload to the storage
                    $certificateImagePath = $certificateImage->store('certificates', 'public');
                }
            } else {
                $certificateImagePath = null;
            }

            // Step 4: Insert the certificate into the database
            Certificate::create([
                'professional_ID' => $professional->user_ID,
                'certificate_name' => $certificateName,
                'certificate_date' => $certificateDate,
                'certificate_image' => $certificateImagePath,
            ]);
        }

        // Step 5: Update the customer's status to 'converted'
        $user->status = 'converted';
        $user->save();

        return response()->json([
            'message' => 'User successfully converted to professional and certificates added.',
            'professional' => $professional,
            'certificates' => $certificateDetails,
        ], 200);
    }
}
