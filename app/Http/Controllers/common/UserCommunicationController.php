<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Helper;


class UserCommunicationController extends Controller
{

    /**
    * Helper method to retrieve the authenticated user's ID.
    */
    private function getAuthenticatedUserId()
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated and return the user_ID
        return $user ? $user->user_ID : null;
    }


    /**
     * Parse the notification IDs from the request.
     * Handles both individual IDs (e.g., "1,2,3") and ranges (e.g., "1-10").
     *
     * @param string $input
     * @return array
     */
    private function parseNotificationIds($input)
    {
        // If input is a range with dash (e.g., "1-10")
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $input, $matches)) {
            // Generate an array of IDs from the range
            return range($matches[1], $matches[2]);
        }

        // Otherwise, assume input is a comma-separated list of IDs (e.g., "1,2,3,4")
        return array_map('intval', explode(',', $input));
    }



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

    /**
    * Mark a notification as read (or unread)
    */

    public function markNotificationAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string', // The ID(s) can be a string (to handle both range and array)
        ]);

        // Get the notification IDs from the request
        $notificationIds = $this->parseNotificationIds($request->notification_id); // Parse the IDs

        // Update notifications to mark as read for the authenticated user
        Notification::whereIn('notification_ID', $notificationIds)
            ->where('user_ID', $this->getAuthenticatedUserId()) // Ensure only the authenticated user can update their notifications
            ->update(['is_read' => 1]);

        return response()->json(['message' => 'Notifications marked as read successfully.'], 200);
    }


    /**
     * Delete a notification
    */

    public function deleteNotification(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string', // The ID(s) can be a string (to handle both range and array)
        ]);

        // Get the notification IDs from the request
        $notificationIds = $this->parseNotificationIds($request->notification_id); // Parse the IDs

        // Delete notifications for the authenticated user
        Notification::whereIn('notification_ID', $notificationIds)
            ->where('user_ID', $this->getAuthenticatedUserId()) // Ensure only the authenticated user can delete their notifications
            ->delete();

        return response()->json(['message' => 'Notifications deleted successfully.'], 200);
    }

}
