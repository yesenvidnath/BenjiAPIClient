<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\IncomeSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
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


    // Auth and login registration

    // User Registration
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'address' => 'nullable|string',
            'type' => 'required|string',
            'DOB' => 'required|date',
            'phone_number' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'type' => $request->type,
            'DOB' => $request->DOB,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $request->profile_image,
            'bank_choice' => $request->bank_choice,
        ]);

        // Automatically log in the user after registration
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

}
