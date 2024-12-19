<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;


class BookProfeshanal extends Controller
{
    /**
     * Book a professional meeting
     */
    public function bookMeeting(Request $request)
    {
        // Ensure only customers or admins can book meetings
        $user = Auth::user();
        if (!in_array($user->type, ['Customer', 'Admin'])) {
            return response()->json(['message' => 'Unauthorized. Only customers and admins can book meetings.'], 403);
        }

        // Validate the incoming request
        $request->validate([
            'professional_id' => 'required|integer|exists:professionals,user_ID',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // Extract customer details from token
        $customerID = $user->user_ID;
        $professionalID = $request->input('professional_id');
        $startTime = $request->input('start_time');
        $meetingID = null;

        try {
            // Call the stored procedure
            DB::statement('CALL BookProfessionalMeeting(?, ?, ?, @meetingID)', [
                $customerID,
                $professionalID,
                $startTime,
            ]);

            // Retrieve the meeting ID from the procedure
            $meetingID = DB::select('SELECT @meetingID AS meetingID')[0]->meetingID;

            if (!$meetingID) {
                return response()->json(['message' => 'Meeting booking failed. Please try again.'], 500);
            }

            // Generate an encrypted payment URL
            $encryptedDetails = Crypt::encryptString(json_encode([
                'user_id' => $user->user_ID,
                'meeting_id' => $meetingID,
                'token' => $request->bearerToken(),
                'price' => 100.00, // Replace with the actual charge_per_hr or another dynamic value
            ]));

            $paymentUrl = "http://127.0.0.1:8000/payment/{$encryptedDetails}";

            // Return a success response
            return response()->json([
                'message' => 'Meeting successfully booked.',
                'meeting_id' => $meetingID,
                'payment_url' => $paymentUrl,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while booking the meeting.', 'error' => $e->getMessage()], 500);
        }
    }
}
