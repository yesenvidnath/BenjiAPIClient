<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

class MeetingController extends Controller
{
    private $zoomAccountId;
    private $zoomClientId;
    private $zoomClientSecret;

    public function __construct()
    {
        // Load Zoom API credentials from .env or configuration
        $this->zoomAccountId = env('ZOOM_ACCOUNT_ID');
        $this->zoomClientId = env('ZOOM_CLIENT_ID');
        $this->zoomClientSecret = env('ZOOM_CLIENT_SECRET');
    }

    public function createMeeting(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'customer_email' => 'required|email',
            'professional_email' => 'required|email',
            'start_date_time' => 'required|date_format:Y-m-d H:i:s',
            'duration' => 'required|integer|min:15', // duration in minutes
        ]);

        try {
            // Get access token
            $accessToken = $this->getZoomAccessToken();

            // Prepare meeting data
            $meetingData = [
                'topic' => 'Professional Meeting',
                'type' => 2, // Scheduled meeting
                'start_time' => date('Y-m-d\TH:i:s', strtotime($request->start_date_time)),
                'duration' => $request->duration,
                'timezone' => 'UTC',
                'agenda' => 'Meeting scheduled through our platform',
                'settings' => [
                    'host_video' => false,
                    'participant_video' => false,
                    'join_before_host' => true,
                    'mute_upon_entry' => false,
                    'watermark' => false,
                    'use_pmi' => false,
                    'approval_type' => 0, // Automatically approve
                    'registration_type' => 1, // Attendees register once and can attend any of the occurrences
                    'auto_recording' => 'none'
                ]
            ];

            // Create meeting via cURL
            $ch = curl_init('https://api.zoom.us/v2/users/me/meetings');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meetingData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Process the response
            if ($httpCode == 201) {
                $meetingDetails = json_decode($response, true);

                // Invite attendees
                $this->inviteAttendees(
                    $accessToken,
                    $meetingDetails['id'],
                    [$request->customer_email, $request->professional_email]
                );

                return response()->json([
                    'message' => 'Meeting successfully created',
                    'meetup_url' => $meetingDetails['join_url'],
                    'meeting_id' => $meetingDetails['id'],
                    'start_time' => $meetingDetails['start_time']
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to create meeting',
                    'error' => $response,
                    'http_code' => $httpCode
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error creating meeting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getZoomAccessToken()
    {
        // Base64 encode the credentials
        $credentials = base64_encode($this->zoomClientId . ':' . $this->zoomClientSecret);

        // Prepare token request
        $ch = curl_init('https://zoom.us/oauth/token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'account_credentials',
            'account_id' => $this->zoomAccountId
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            throw new Exception('Failed to obtain access token: ' . $response);
        }

        $tokenData = json_decode($response, true);
        return $tokenData['access_token'];
    }

    private function inviteAttendees($accessToken, $meetingId, $emails)
    {
        // Prepare attendees invitation
        $inviteData = [
            'action' => 'invite',
            'attendees' => array_map(function($email) {
                return ['email' => $email];
            }, $emails)
        ];

        // Send invitation via cURL
        $ch = curl_init("https://api.zoom.us/v2/meetings/{$meetingId}/invite");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inviteData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function updateMeeting(Request $request, $meetingId)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'topic' => 'sometimes|string',
            'start_time' => 'sometimes|date_format:Y-m-d H:i:s',
            'duration' => 'sometimes|integer|min:15'
        ]);

        try {
            // Get access token
            $accessToken = $this->getZoomAccessToken();

            // Prepare update data
            $updateData = array_filter([
                'topic' => $request->topic ?? null,
                'start_time' => $request->start_time ? date('Y-m-d\TH:i:s', strtotime($request->start_time)) : null,
                'duration' => $request->duration ?? null
            ]);

            // Update meeting via cURL
            $ch = curl_init("https://api.zoom.us/v2/meetings/{$meetingId}");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 204) {
                return response()->json([
                    'message' => 'Meeting successfully updated'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to update meeting',
                    'error' => $response,
                    'http_code' => $httpCode
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error updating meeting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteMeeting($meetingId)
    {
        try {
            // Get access token
            $accessToken = $this->getZoomAccessToken();

            // Delete meeting via cURL
            $ch = curl_init("https://api.zoom.us/v2/meetings/{$meetingId}");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 204) {
                return response()->json([
                    'message' => 'Meeting successfully deleted'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete meeting',
                    'error' => $response,
                    'http_code' => $httpCode
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting meeting',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
