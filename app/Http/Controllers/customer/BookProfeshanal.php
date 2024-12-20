<?php

namespace App\Http\Controllers\customer;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

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


        /**
     * Finalize the payment and setup the meeting.
     */
    public function finalizeMeetingPayment(Request $request, MeetingController $meetingController)
    {
        $request->validate([
            'meeting_id' => 'required|integer',
            'payment_amount' => 'required|numeric',
        ]);

        $meetingId = $request->meeting_id;
        $paymentAmount = $request->payment_amount;

        try {
            // Step 1: Retrieve the meeting details from the database
            $meeting = DB::table('meetings')->where('meeting_ID', $meetingId)->first();

            if (!$meeting) {
                return response()->json(['message' => 'Meeting not found'], 404);
            }

            // Step 2: Retrieve professional and customer details
            $professional = DB::table('users')->where('user_ID', $meeting->user_ID_professional)->first();
            $customer = DB::table('users')->where('user_ID', $meeting->user_ID_customer)->first();

            if (!$professional || !$customer) {
                return response()->json(['message' => 'Professional or customer details not found'], 404);
            }

            // Step 3: Create Zoom meeting using MeetingController
            $meetingRequest = new Request([
                'customer_email' => $customer->email,
                'professional_email' => $professional->email,
                'start_date_time' => $meeting->start_time_date,
                'duration' => 60, // Assume 1-hour meeting
            ]);

            $meetingResponse = $meetingController->createMeeting($meetingRequest);
            $meetingData = json_decode($meetingResponse->getContent(), true);

            if (!isset($meetingData['meetup_url'])) {
                return response()->json(['message' => 'Failed to create Zoom meeting', 'error' => $meetingData], 500);
            }

            $meetUrl = $meetingData['meetup_url'];

            // Step 4: Update the meetings table with Zoom details
            DB::table('meetings')->where('meeting_ID', $meetingId)->update([
                'meet_url' => $meetUrl,
                'status' => 'pending',
                'updated_at' => now(),
            ]);

            // Step 5: Trigger the stored procedure to handle notifications and expenses
            DB::statement('CALL FinalizeMeetingPayment(?, ?, ?)', [$meetingId, $meetUrl, $paymentAmount]);

            return response()->json(['message' => 'Meeting finalized successfully', 'meet_url' => $meetUrl], 200);
        } catch (\Exception $e) {
            Log::error('Error finalizing meeting payment: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while finalizing the meeting', 'error' => $e->getMessage()], 500);
        }
    }
}
