<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Post;
use App\Models\Adoption;
use App\Models\User;
use App\Models\AnimalCase;
use App\Models\Appointment;
use App\Notifications\AdApprovedNotification;
use App\Notifications\AdobtStatusAccept;
use App\Notifications\PostStatusUpdated;
use App\Notifications\DonationStatusNotification;
use App\Notifications\ImmediateCaseNotification;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Services\FCMService;
use App\Models\Donate;
use App\Notifications\AppointmentStatusNotification; // تأكد من استيرادها في الأعلى

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
            $user->notify(new AdApprovedNotification($ad));
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
        $adoption->loadMissing('user', 'animal');

        $user = $adoption->user;

        // إرسال إشعار Laravel (mail + database + fcm via Notification class)
        try {
            $user->notify(new AdobtStatusAccept($adoption));
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال إشعار Laravel: ' . $e->getMessage());
        }

        // إرسال إشعار FCM يدوي إن أردت إرسال إضافي (اختياري)
        if ($user->fcm_token) {
            try {
                $messaging = app('firebase.messaging');

                $message = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(FirebaseNotification::create(
                        'حالة طلب التبني',
                        'تمت ' . ($adoption->status === 'approved' ? 'الموافقة' : 'رفض') . ' طلب التبني الخاص بك'
                    ))
                    ->withData([
                        'adoption_id' => (string) $adoption->id,
                        'type' => 'adoption_' . $adoption->status,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]);

                $messaging->send($message);
            } catch (\Exception $e) {
                Log::error('خطأ في إرسال إشعار FCM: ' . $e->getMessage());
            }
        }
    }

    public function sendPostStatusNotification(Post $post, string $action): void
    {
        $user = $post->user;

        $user->notify(new PostStatusUpdated($post, $action));

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
public function sendDonationStatusNotification(Donate $donation, string $status): bool
{
    $user = $donation->user;

    if (!$user) {
        \Log::error("لم يتم العثور على مستخدم مرتبط بالتبرع رقم {$donation->id}");
        return false; // أو رمي استثناء إذا تريد
    }

    try {
        $user->notify(new DonationStatusNotification($donation, $status));

        if ($user->fcm_token) {
            // ارسال إشعار FCM كما في الكود السابق
        }

        return true;
    } catch (\Exception $e) {
        \Log::error("فشل إرسال إشعار التبرع", [
            'donation_id' => $donation->id,
            'error' => $e->getMessage()
        ]);

        return false;
    }
}
    
public function sendImmediateCaseNotification(User $employee, AnimalCase $case, Appointment $appointment)
{
    try {
        // إرسال إشعار Laravel العادي
        $employee->notify(new ImmediateCaseNotification($case, $appointment));

        // إرسال إشعار FCM إذا كان الموظف لديه token
        if ($employee->fcm_token) {
            $messaging = app('firebase.messaging');
            
            $message = CloudMessage::withTarget('token', $employee->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    'طلب فوري جديد',
                    'حالة طارئة لـ ' . $case->name_animal . ' - ' . $case->case_type
                ))
                ->withData([
                    'type' => 'immediate_case',
                    'case_id' => (string) $case->id,
                    'appointment_id' => (string) $appointment->id,
                    'animal_name' => $case->name_animal,
                    'case_type' => $case->case_type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]);

            $messaging->send($message);
        }
    } catch (\Exception $e) {
        Log::error('فشل إرسال إشعار الحالة الفورية', [
            'employee_id' => $employee->id,
            'case_id' => $case->id,
            'error' => $e->getMessage()
        ]);
    }
}


public function sendAppointmentStatusNotification(Appointment $appointment, string $status): bool
{
    $user = $appointment->user;

    if (!$user) {
        Log::error("الموعد رقم {$appointment->id} لا يحتوي على مستخدم.");
        return false;
    }

    try {
        // إرسال إشعار Laravel (قاعدة البيانات + البريد إن وجد)
        $user->notify(new AppointmentStatusNotification($appointment, $status));

        // إشعار FCM إن وُجد
        if ($user->fcm_token) {
            $messaging = app('firebase.messaging');

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    'تحديث حالة الموعد',
                    'تم ' . ($status === 'approved' ? 'قبول' : 'رفض') . ' موعدك رقم ' . $appointment->id
                ))
                ->withData([
                    'type' => 'appointment_status',
                    'appointment_id' => (string) $appointment->id,
                    'status' => $status,
                    'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]);

            $messaging->send($message);
        }

        return true;
    } catch (\Exception $e) {
        Log::error("فشل إرسال إشعار حالة الموعد رقم {$appointment->id}", [
            'error' => $e->getMessage()
        ]);
        return false;
    }
}
}
