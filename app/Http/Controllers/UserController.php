<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Models\Professional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Helper method to retrieve the authenticated user's ID.
     */

    public function getAuthenticatedUserId()
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated and return the user_ID
        return $user ? $user->user_ID : null;
    }

    // Retrieve the professional ID using authenticated user ID
    private function getProfessionalId()
    {
        $userId = $this->getAuthenticatedUserId();
        $professional = Professional::where('user_ID', $userId)->first();

        return $professional ? $professional->professional_ID : null;
    }

    // Auth and login registration

    // User Registration
    public function register(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:Customer,Professional,Admin',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'address' => 'nullable|string',
            'DOB' => 'required|date',
            'phone_number' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string',
            'certificateID' => 'nullable|string', // Only used if type is Professional
            'adminDescription' => 'nullable|string', // Only used if type is Admin
            'incomeSourceName' => 'nullable|string', // Only used if type is Professional or Customer
            'incomeAmount' => 'nullable|numeric',
            'incomeFrequency' => 'nullable|string|in:monthly,annual',
            'incomeDescription' => 'nullable|string',
        ]);

        // Prepare parameters for the stored procedure
        $type = $request->type;
        $first_name = $request -> first_name;
        $last_name = $request-> last_name;
        $address = $request->address;
        $DOB = $request->DOB;
        $phone = $request->phone_number;
        $email = $request->email;
        $password = Hash::make($request->password); // Hash password before passing to the procedure
        $profileImage = $request->profile_image;
        $bankChoice = $request->bank_choice;

        // Additional parameters based on user type
        $certificateID = $type === 'Professional' ? $request->certificateID : null;
        $adminDescription = $type === 'Admin' ? $request->adminDescription : null;
        $incomeSourceName = $type !== 'Admin' ? $request->incomeSourceName : null;
        $incomeAmount = $type !== 'Admin' ? $request->incomeAmount : null;
        $incomeFrequency = $type !== 'Admin' ? $request->incomeFrequency : null;
        $incomeDescription = $type !== 'Admin' ? $request->incomeDescription : null;

        // Call the stored procedure
        DB::statement("CALL CreateUserAccount(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $type,
            $first_name,
            $last_name,
            $address,
            $DOB,
            $phone,
            $email,
            $password,
            $profileImage,
            $bankChoice,
            $certificateID,
            $adminDescription,
            $incomeSourceName,
            $incomeAmount,
            $incomeFrequency,
            $incomeDescription,
        ]);

        // Retrieve the new user by email to generate a token
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User registration failed'], 500);
        }

        // Generate token for the newly registered user
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }


    public function login(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Retrieve the user ID explicitly to ensure it’s valid
        $userId = User::where('email', $request->email)->value('user_ID');

        if (!$userId) {
            return response()->json(['error' => 'User ID is missing'], 500);
        }

        // Generate a new token
        $token = $user->createToken('api_token')->plainTextToken;

        // Return a simple success message with the token
        return response()->json(['message' => 'Login successful', 'token' => $token], 200);
    }

    public function getAuthenticatedUser(Request $request)
    {
        // Retrieve the authenticated user
        $user = $request->user();

        // Return user details
        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    }

    // User Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }


    /**
    * Reset & Change Password
    */

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        // Initiate password reset process
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email.'], 200);
        } else {
            return response()->json(['message' => 'Unable to send reset link. Please try again.'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }








    public function updateProfile(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'address' => 'nullable|string',
            'type' => 'nullable|string',
            'DOB' => 'nullable|date',
            'phone_number' => 'nullable|string|unique:users,phone_number,' . $request->user()->user_ID . ',user_ID',
            'email' => 'nullable|string|email|unique:users,email,' . $request->user()->user_ID . ',user_ID',
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Update user details
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'type' => $request->type ?? $user->type,
            'DOB' => $request->DOB ?? $user->DOB,
            'phone_number' => $request->phone_number ?? $user->phone_number,
            'email' => $request->email ?? $user->email,
            'profile_image' => $request->profile_image ?? $user->profile_image,
            'bank_choice' => $request->bank_choice ?? $user->bank_choice,
        ]);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }


    // Get Profile by ID
    public function getProfile($id)
    {
        $user = User::where('user_ID', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }

    // Delete Profile
    public function deleteProfile(Request $request)
    {
        // Check if 'user_IDs' query parameter is provided for bulk delete
        $userIds = $request->query('user_IDs');

        if ($userIds) {
            // Split the IDs by comma in case multiple IDs are provided
            $idsArray = explode(',', $userIds);

            // Delete users with the specified IDs
            $deletedCount = User::whereIn('user_ID', $idsArray)->delete();

            return response()->json([
                'message' => "$deletedCount user(s) deleted successfully",
                'deleted_user_IDs' => $idsArray,
            ], 200);
        }

        // If no user_IDs are provided, delete the authenticated user's profile
        $user = $request->user();
        $user->delete();

        return response()->json(['message' => 'Your profile has been deleted successfully'], 200);
    }


    public function searchProfiles(Request $request)
    {
        $query = $request->query('query');

        $users = User::where('user_ID', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('phone_number', 'like', "%$query%")
            ->orWhere('type', 'like', "%$query%")
            ->orWhere('first_name', 'like', "%$query%")
            ->orWhere('last_name', 'like', "%$query%")
            ->get();

        return response()->json(['users' => $users], 200);
    }









    // Add Certification
    public function addCertification(Request $request)
    {
        $request->validate([
            'certificate_name' => 'required|string',
            'certificate_date' => 'required|date',
            'certificate_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Only images allowed
        ]);

        $professionalId = $this->getProfessionalId();

        if (!$professionalId) {
            return response()->json(['message' => 'Professional ID not found'], 404);
        }

        // Store the certification image
        $imagePath = $request->file('certificate_image')->store('public/certifications');
        $imageUrl = Storage::url($imagePath);

        // Save certification details in the database
        $certificate = Certificate::create([
            'professional_ID' => $professionalId,
            'certificate_name' => $request->certificate_name,
            'certificate_date' => $request->certificate_date,
            'certificate_image' => $imageUrl,
        ]);

        return response()->json(['message' => 'Certification added successfully', 'certificate' => $certificate], 201);
    }


    // Update Certification
    public function updateCertification(Request $request, $id)
    {
        $request->validate([
            'certificate_name' => 'nullable|string',
            'certificate_date' => 'nullable|date',
            'certificate_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $certificate = Certificate::find($id);

        if (!$certificate) {
            return response()->json(['message' => 'Certification not found'], 404);
        }

        // Check ownership
        if ($certificate->professional_ID !== $this->getProfessionalId()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        // Update the image if provided
        if ($request->hasFile('certificate_image')) {
            // Delete old image
            if ($certificate->certificate_image) {
                Storage::delete(str_replace('/storage', 'public', $certificate->certificate_image));
            }
            $imagePath = $request->file('certificate_image')->store('public/certifications');
            $certificate->certificate_image = Storage::url($imagePath);
        }

        // Update other fields
        $certificate->update($request->only('certificate_name', 'certificate_date'));

        return response()->json(['message' => 'Certification updated successfully', 'certificate' => $certificate], 200);
    }


    // Delete Certification
    public function deleteCertification($id)
    {
        $certificate = Certificate::find($id);

        if (!$certificate) {
            return response()->json(['message' => 'Certification not found'], 404);
        }

        // Check ownership
        if ($certificate->professional_ID !== $this->getProfessionalId()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        // Delete the image from storage
        if ($certificate->certificate_image) {
            Storage::delete(str_replace('/storage', 'public', $certificate->certificate_image));
        }

        $certificate->delete();

        return response()->json(['message' => 'Certification deleted successfully'], 200);
    }

    // Get Certification by ID
    public function getCertification($id)
    {
        $certificate = Certificate::find($id);

        if (!$certificate) {
            return response()->json(['message' => 'Certification not found'], 404);
        }

        // Check ownership
        if ($certificate->professional_ID !== $this->getProfessionalId()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        return response()->json(['certificate' => $certificate], 200);
    }

    // Search Certifications
    public function searchCertification(Request $request)
    {
        $query = $request->query('query');
        $professionalId = $this->getProfessionalId();

        if (!$professionalId) {
            return response()->json(['message' => 'Professional ID not found'], 404);
        }

        $certificates = Certificate::where('professional_ID', $professionalId)
            ->where(function ($q) use ($query) {
                $q->where('certificate_name', 'like', "%$query%")
                    ->orWhere('certificate_date', 'like', "%$query%");
            })
            ->get();

        return response()->json(['certificates' => $certificates], 200);
    }
}
