<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\IncomeSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
            'type' => 'required|string',
            'DOB' => 'required|date',
            'phone_number' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string',
        ]);

        $user = User::create([
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


    public function updateProfile(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'type' => 'nullable|string',
            'DOB' => 'nullable|date',
            'phone_number' => 'nullable|string|unique:users,phone_number,' . $request->user()->id,
            'email' => 'nullable|string|email|unique:users,email,' . $request->user()->id,
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Update user details
        $user->update([
            'type' => $request->type ?? $user->type,
            'DOB' => $request->DOB ?? $user->DOB,
            'phone_number' => $request->phone_number ?? $user->phone_number,
            'email' => $request->email ?? $user->email,
            'profile_image' => $request->profile_image ?? $user->profile_image,
            'bank_choice' => $request->bank_choice ?? $user->bank_choice,
        ]);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }


    // User Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }



    // Add Income Source (called after user registers and logs in)
    public function addIncomeSource(Request $request)
    {
        $request->validate([
            'source_name' => 'required|string',
            'amount' => 'required|numeric',
            'frequency' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Use the helper method to get the authenticated user's ID
        $userId = $this->getAuthenticatedUserId();

        // Check if we successfully retrieved the user ID
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Create a new Income Source for the authenticated user
        $incomeSource = IncomeSource::create([
            'user_ID' => $userId,
            'source_name' => $request->source_name,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Income source added successfully', 'income_source' => $incomeSource], 201);
    }
}
