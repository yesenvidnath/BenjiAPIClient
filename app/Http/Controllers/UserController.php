<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // User Registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:Customer,Professional,Admin',
            'DOB' => 'nullable|date',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'type' => $request->type,
            'DOB' => $request->DOB,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $request->profile_image,
            'bank_choice' => $request->bank_choice,
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    // User Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login successful', 'access_token' => $token, 'user' => $user], 200);
    }

    // Get User Profile
    public function profile()
    {
        $user = Auth::user();

        return response()->json(['user' => $user], 200);
    }

    // Update User Profile
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|max:20',
            'DOB' => 'nullable|date',
            'profile_image' => 'nullable|string',
            'bank_choice' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['phone_number', 'DOB', 'profile_image', 'bank_choice']));

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }

    // Logout
    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
