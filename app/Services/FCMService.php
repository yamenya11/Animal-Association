<?php   
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;


class FCMService
{
    
    protected $messaging;

    public function __construct()
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('services.fcm.credentials'));
                
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('فشل تهيئة خدمة FCM: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendToToken($token, $data)
    {
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
}