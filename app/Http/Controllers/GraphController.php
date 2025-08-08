<?php

namespace App\Http\Controllers;

use App\Providers\GraphHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GraphController extends Controller
{
    public function authenticate()
    {
        try {
            GraphHelper::initializeGraphForUserAuth();
            $authData = GraphHelper::getDeviceCodeData();

            return response()->json([
                'message' => 'Authentication initiated. Use the device code and URL to authenticate.',
                'device_code' => $authData['device_code'],
                'verification_url' => $authData['verification_uri'],
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error initiating authentication: ' . $e->getMessage()], 500);
        }
    }

     public function acquireAccessToken()
     {
         try {
             // Call getUserToken after device authentication
             $token = GraphHelper::getUserToken();
 
             return response()->json(['access_token' => $token], 200);
         } catch (Exception $e) {
             return response()->json(['error' => 'Error retrieving access token: ' . $e->getMessage()], 500);
         }
     }



    // Store the authenticated token in cache (or session) for later use
    private function cacheToken($token)
    {
        Cache::put('graph_access_token', $token, 3600); // Store token for 1 hour
    }

    // Check if we have a cached token and it's valid
    private function getToken()
    {
        return Cache::get('graph_access_token');
    }

    // Retrieve inbox messages using cached token
    public function getInbox()
    {
        try {
            // Check for cached token
            $token = $this->getToken();

            if (!$token) {
                return response()->json(['error' => 'User not authenticated. Please authenticate first.'], 401);
            }

            // Set the token on the GraphHelper (assuming it can accept a cached token)
            GraphHelper::$tokenProvider->setAccessToken($token);

            // Fetch inbox messages
            $messages = GraphHelper::getInbox();

            return response()->json(['messages' => $messages]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error fetching inbox: ' . $e->getMessage()], 500);
        }
    }
}
