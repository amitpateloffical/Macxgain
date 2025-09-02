<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class TrueDataAuthService
{
    private $username;
    private $password;
    private $authUrl;
    private $accessToken;
    private $tokenExpiry;

    public function __construct()
    {
        $this->username = config('services.truedata.username');
        $this->password = config('services.truedata.password');
        $this->authUrl = 'https://auth.truedata.in/token';
        
        if (!$this->username || !$this->password) {
            throw new Exception('TrueData credentials not configured');
        }
    }

    /**
     * Get valid access token
     */
    public function getAccessToken()
    {
        // Check if we have a valid cached token
        $cachedToken = Cache::get('truedata_access_token');
        if ($cachedToken && $this->isTokenValid($cachedToken)) {
            $this->accessToken = $cachedToken['access_token'];
            $this->tokenExpiry = $cachedToken['expires_at'];
            return $this->accessToken;
        }

        // Get new token
        return $this->authenticate();
    }

    /**
     * Authenticate with TrueData and get access token
     */
    public function authenticate()
    {
        try {
            Log::info('Authenticating with TrueData...');

            $response = Http::asForm()->post($this->authUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password'
            ]);

            if (!$response->successful()) {
                throw new Exception('Authentication failed: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                throw new Exception('Invalid authentication response: ' . json_encode($data));
            }

            $this->accessToken = $data['access_token'];
            $this->tokenExpiry = now()->addSeconds($data['expires_in'] ?? 3600);

            // Cache the token
            Cache::put('truedata_access_token', [
                'access_token' => $this->accessToken,
                'expires_at' => $this->tokenExpiry,
                'expires_in' => $data['expires_in'] ?? 3600
            ], $this->tokenExpiry);

            Log::info('TrueData authentication successful');
            return $this->accessToken;

        } catch (Exception $e) {
            Log::error('TrueData authentication failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if token is valid
     */
    private function isTokenValid($tokenData)
    {
        if (!isset($tokenData['expires_at'])) {
            return false;
        }

        $expiry = \Carbon\Carbon::parse($tokenData['expires_at']);
        return $expiry->isFuture() && $expiry->diffInMinutes(now()) > 5; // 5 minute buffer
    }

    /**
     * Get authorization header
     */
    public function getAuthHeader()
    {
        $token = $this->getAccessToken();
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Clear cached token
     */
    public function clearToken()
    {
        Cache::forget('truedata_access_token');
        $this->accessToken = null;
        $this->tokenExpiry = null;
    }

    /**
     * Test authentication
     */
    public function testAuth()
    {
        try {
            $token = $this->getAccessToken();
            return [
                'success' => true,
                'message' => 'Authentication successful',
                'token_length' => strlen($token),
                'expires_at' => $this->tokenExpiry?->toISOString()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ];
        }
    }
}
