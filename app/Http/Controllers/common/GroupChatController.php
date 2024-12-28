<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GroupChatController extends Controller
{
    private $accountId;
    private $clientId;
    private $clientSecret;
    private $baseUrl = 'https://api.zoom.us/v2';
    private $tokenUrl = 'https://zoom.us/oauth/token';

    public function __construct()
    {
        $this->accountId = env('ZOOM_ACCOUNT_ID');
        $this->clientId = env('ZOOM_CLIENT_ID');
        $this->clientSecret = env('ZOOM_CLIENT_SECRET');
    }

    private function getAccessToken()
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post($this->tokenUrl, [
                    'grant_type' => 'account_credentials',
                    'account_id' => $this->accountId
                ]);

            Log::info('Token response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get Zoom access token: ' . $response->body());
            }

            $data = $response->json();
            if (!isset($data['access_token'])) {
                throw new \Exception('Access token not found in response');
            }

            return $data['access_token'];
        } catch (\Exception $e) {
            Log::error('Error getting Zoom access token', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function createGroupChat(Request $request)
    {
        try {
            Log::info('Creating group chat', ['request' => $request->all()]);

            $validatedData = $request->validate([
                'emails' => 'required|array',
                'emails.*' => 'email',
                'name' => 'required|string'
            ]);

            $accessToken = $this->getAccessToken();

            // First get the current user's info
            $userResponse = Http::withToken($accessToken)
                ->get($this->baseUrl . '/users/me');

            if (!$userResponse->successful()) {
                throw new \Exception('Failed to get user info: ' . $userResponse->body());
            }

            $userId = $userResponse->json()['id'];
            $userEmail = $userResponse->json()['email'];

            // Generate unique IDs for each user
            $members = array_map(function($email) {
                return [
                    'email' => $email,
                    'id' => Str::uuid()->toString()
                ];
            }, array_merge($validatedData['emails'], [$userEmail]));

            // Create channel data
            $channelData = [
                'name' => $validatedData['name'],
                'members' => $members,
                'type' => 2 // This sets the channel type to group chat
            ];

            Log::info('Creating Zoom chat channel', ['data' => $channelData]);

            // Create the chat channel using the correct endpoint
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/chat/users/me/channels', $channelData);

            Log::info('Channel creation response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                throw new \Exception('Zoom API error: ' . $response->body());
            }

            $channelInfo = $response->json();

            // Get channel URL
            $channelUrl = sprintf(
                'https://zoom.us/chat/channel/%s',
                $channelInfo['id']
            );

            return response()->json([
                'success' => true,
                'channel_id' => $channelInfo['id'],
                'channel_name' => $channelInfo['name'],
                'channel_url' => $channelUrl,
                'members' => $members, // Return members with their unique IDs
                'debug_info' => [
                    'channel_response' => $channelInfo
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Group chat creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create group chat: ' . $e->getMessage(),
                'debug_info' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }




    public function validateGroupAccess(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'channel_id' => 'required|string',
                'email' => 'required|email'
            ]);

            $accessToken = $this->getAccessToken();

            // Check channel membership
            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . '/chat/channels/' . $validatedData['channel_id'] . '/members');

            if ($response->successful()) {
                $members = $response->json()['members'];
                $isMember = collect($members)->contains('email', $validatedData['email']);

                return response()->json([
                    'success' => true,
                    'has_access' => $isMember
                ]);
            }

            throw new \Exception('Failed to validate access: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Access validation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getUserEmailByChannelId($channelId)
    {
        try {
            $accessToken = $this->getAccessToken();

            // Fetch channel members
            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . '/chat/channels/' . $channelId . '/members');

            if ($response->successful()) {
                $members = $response->json()['members'];

                // Assuming you have a way to identify the current user from the members list
                // For now, we can return the first email as an example
                $userEmail = $members[0]['email'] ?? null;

                if ($userEmail) {
                    return response()->json(['success' => true, 'email' => $userEmail]);
                } else {
                    throw new \Exception('User email not found for the given channelId');
                }
            }

            throw new \Exception('Failed to fetch members for the given channelId: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Error fetching user email by channelId', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function chatView($id)
    {
        return view('group-chat', ['channelId' => $id]);
    }

    public function getChatMessages($channelId)
    {
        $accessToken = $this->generateZoomAccessToken();

        $response = Http::withToken($accessToken)->get("https://api.zoom.us/v2/chat/channels/{$channelId}/messages");

        if ($response->successful()) {
            $messages = $response->json()['messages'];
            return response()->json($messages);
        }

        return response()->json(['error' => 'Failed to load messages.'], $response->status());
    }


    public function showGroupChatView($id, $email)
    {
        // Generate chat box URL or provide other logic as needed
        $chatboxUrl = "https://zoom.us/groupchat/{$id}?email={$email}";

        // Pass the required data to the view
        return view('common.group-chat', [
            'channelId' => $id,
            'userEmail' => $email,
            'chatboxUrl' => $chatboxUrl, // Remove this if you're fetching messages via API
        ]);
    }



}
