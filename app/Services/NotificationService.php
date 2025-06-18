<?php



namespace App\Services;

use App\Models\Ad;
use App\Notifications\AdApprovedNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class NotificationService
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function sendAdApprovedNotification(Ad $ad)
    {
        $user = $ad->user;

        try {
            // إرسال الإشعار عبر نظام Laravel
            $user->notify(new AdApprovedNotification($ad));
            
            // تسجيل الحدث في السجلات
            Log::info("تم إرسال إشعار الموافقة للإعلان {$ad->id} للمستخدم {$user->id}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("فشل إرسال الإشعار: " . $e->getMessage(), [
                'ad_id' => $ad->id,
                'user_id' => $user->id
            ]);
            
            return false;
        }
    }


    public function sendAdoptionStatusNotification(Adoption $adoption)
{
    $user = $adoption->user;

    // إشعار البريد وقاعدة البيانات
    $user->notify(new AdobtStatusAccept($adoption));

    // إشعار FCM
    if ($user->fcm_token) {
        try {
            $messaging = app('firebase.messaging');

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    'تمت الموافقة على طلب التبني',
                    'تمت الموافقة على طلبك لتبني الحيوان: ' . ($adoption->animal->name ?? '')
                ))
                ->withData([
                    'adoption_id' => (string) $adoption->id,
                    'type' => 'adoption_approved',
                ]);

            $messaging->send($message);
        } catch (\Exception $e) {
            \Log::error('خطأ في إرسال إشعار FCM: ' . $e->getMessage());
        }
    }
}

public function sendPostStatusNotification(Post $post, string $action): void
{
    $user = $post->user;

    // Laravel Notification
    $user->notify(new PostStatusUpdated($post, $action));

    // FCM Notification
    if ($user->fcm_token) {
        try {
            $messaging = app('firebase.messaging');

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    $action === 'approved' ? 'تمت الموافقة على منشورك' : 'تم رفض منشورك',
                    'عنوان المنشور: ' . $post->title
                ))
                ->withData([
                    'post_id' => (string) $post->id,
                    'type' => 'post_status_update',
                    'status' => $action
                ]);

            $messaging->send($message);
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال إشعار FCM للمنشور: ' . $e->getMessage());
        }
    }
}
}
