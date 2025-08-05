<?php

namespace App\Services;

use Kreait\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = Firebase::messaging();
        
    }

    public function sendToToken($token, $data)
    {
        if (empty($token)) {
            Log::warning('FCM Token فارغ');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(FirebaseNotification::create(
                    $data['title'] ?? '',
                    $data['body'] ?? ''
                ))
                ->withData($data['data'] ?? []);

            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage(), [
                'token' => $token,
                'data' => $data
            ]);
            return false;
        }
    }

    public function sendToTokens(array $tokens, array $data)
    {
        $validTokens = array_filter($tokens);
        if (empty($validTokens)) {
            Log::warning('لا توجد أجهزة صالحة للإرسال');
            return false;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create(
                    $data['title'] ?? '',
                    $data['body'] ?? ''
                ))
                ->withData($data['data'] ?? []);

            $this->messaging->sendMulticast($message, $validTokens);
            return true;
        } catch (\Exception $e) {
            Log::error('FCM Multicast Error: ' . $e->getMessage());
            return false;
        }
    }
}
