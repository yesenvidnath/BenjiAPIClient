<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserCommunicationController extends Controller
{
    /**
     * Send Notification to Specific Users
    **/
    public function sendNotification(Request $request)
    {
        $request->validate([
            'type' => 'required|in:meeting,payment,general',
            'message' => 'required|string|max:255',
            'user_ID' => 'nullable|integer',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|string|email',
            'name' => 'nullable|string',
            'NIC' => 'nullable|string|max:20',
            'user_type' => 'nullable|in:Customer,Professional,Admin',
        ]);

        // Build the query to find specific users based on provided parameters
        $query = User::query();

        if ($request->user_ID) {
            $query->where('user_ID', $request->user_ID);
        }
        if ($request->phone_number) {
            $query->where('phone_number', $request->phone_number);
        }
        if ($request->email) {
            $query->where('email', $request->email);
        }
        if ($request->name) {
            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$request->name}%");
        }
        if ($request->NIC) {
            $query->where('NIC', $request->NIC); // Assuming NIC is a column in users table
        }
        if ($request->user_type) {
            $query->where('type', $request->user_type);
        }

        $users = $query->get();

        // Send notification to each matched user
        foreach ($users as $user) {
            Notification::create([
                'user_ID' => $user->user_ID,
                'type' => $request->type,
                'message' => $request->message,
            ]);
        }

        return response()->json(['message' => 'Notification(s) sent successfully'], 200);
    }

    /**
    * Send Bulk Notifications
    **/
    public function sendBulkNotification(Request $request)
    {
        $request->validate([
            'type' => 'required|in:meeting,payment,general',
            'message' => 'required|string|max:255',
            'user_type' => 'nullable|in:Customer,Professional,Admin',
            'start_user_ID' => 'nullable|integer',
            'end_user_ID' => 'nullable|integer',
        ]);

        // Build query to select users for bulk notification
        $query = User::query();

        if ($request->user_type) {
            $query->where('type', $request->user_type);
        }
        if ($request->start_user_ID && $request->end_user_ID) {
            $query->whereBetween('user_ID', [$request->start_user_ID, $request->end_user_ID]);
        }

        $users = $query->get();

        // Send notification to each user
        foreach ($users as $user) {
            Notification::create([
                'user_ID' => $user->user_ID,
                'type' => $request->type,
                'message' => $request->message,
            ]);
        }

        return response()->json(['message' => 'Bulk notification sent successfully'], 200);
    }
}
