<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Providers\GraphHelper;
use Exception;

class AuthController extends Controller
{
    public function displayAccessToken()
    {
        try {
            // Initialize the Graph client for authentication
            GraphHelper::initializeGraphForUserAuth();

            // Retrieve the access token using the device code flow
            $token = GraphHelper::getUserToken();

            // Display the token
            return response()->json(['User token' => $token], 200);
        } catch (Exception $e) {  dd($e);
            return response()->json(['Error getting access token' => $e->getMessage()], 500);
        }
    }
}
