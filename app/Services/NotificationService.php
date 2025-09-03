<?php

namespace App\Services;

use App\Models\{
    Ad,
    Post,
    Adoption,
    User,
    AnimalCase,
    Appointment,
    Donate
};
use App\Notifications\{
    AdApprovedNotification,
    AdobtStatusAccept,
    PostStatusUpdated,
    DonationStatusNotification,
    ImmediateCaseNotification,
    AppointmentStatusNotification
};
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\{
    CloudMessage,
    Notification as FirebaseNotification
};
use Kreait\Firebase\Messaging\Messaging;
use Throwable;
use Kreait\Firebase\Facades\Firebase;

class NotificationService
{
   
    /**
     * إرسال إشعار بالموافقة على الإعلان
     */
  public function sendAdApprovedNotification($ad)
    {
        $user = $ad->user;

        if (!$user) return false;

        // الإشعار في DB و FCM تلقائياً
        $user->notify(new AdApprovedNotification($ad));

        return true;
    }
    /**
     * إرسال إشعار بتحديث حالة التبني
     */
    public function sendAdoptionStatusNotification(Adoption $adoption): bool
    {
        try {
            $user = $adoption->user;
            $user->notify(new AdobtStatusAccept($adoption));
            
            $statusText = $adoption->status === 'approved' ? 'الموافقة' : 'الرفض';
            
            $this->sendFcmNotification(
                $user,
                'حالة طلب التبني',
                "تمت {$statusText} على طلب التبني الخاص بك",
                [
                    'adoption_id' => (string) $adoption->id,
                    'type' => 'adoption_' . $adoption->status,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]
            );
            
            return true;
        } catch (Throwable $e) {
            Log::error('فشل إرسال إشعار حالة التبني', [
                'adoption_id' => $adoption->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * إرسال إشعار بتحديث حالة المنشور
     */
    public function sendPostStatusNotification(Post $post, string $action): bool
    {
        try {
            $user = $post->user;
            $user->notify(new PostStatusUpdated($post, $action));
            
            $title = $action === 'approved' ? 'تمت الموافقة على منشورك' : 'تم رفض منشورك';
            
            $this->sendFcmNotification(
                $user,
                $title,
                'عنوان المنشور: ' . $post->title,
                [
                    'post_id' => (string) $post->id,
                    'type' => 'post_status_update',
                    'status' => $action
                ]
            );
            
            return true;
        } catch (Throwable $e) {
            Log::error('فشل إرسال إشعار حالة المنشور', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * إرسال إشعار بتحديث حالة التبرع
     */
    public function sendDonationStatusNotification(Donate $donation, string $status): bool
    {
        try {
            $user = $donation->user;
            
            if (!$user) {
                throw new \Exception("لم يتم العثور على مستخدم مرتبط بالتبرع");
            }
            
            $user->notify(new DonationStatusNotification($donation, $status));
            
            $statusText = $status === 'approved' ? 'قبول' : 'رفض';
            
            $this->sendFcmNotification(
                $user,
                'تحديث حالة التبرع',
                "تم {$statusText} تبرعك رقم {$donation->id}",
                [
                    'donation_id' => (string) $donation->id,
                    'type' => 'donation_status',
                    'status' => $status
                ]
            );
            
            return true;
        } catch (Throwable $e) {
            Log::error('فشل إرسال إشعار حالة التبرع', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * إرسال إشعار حالة طارئة
     */
    public function sendImmediateCaseNotification(User $employee, AnimalCase $case, Appointment $appointment): bool
    {
        try {
            $employee->notify(new ImmediateCaseNotification($case, $appointment));
            
            $this->sendFcmNotification(
                $employee,
                'طلب فوري جديد',
                'حالة طارئة لـ ' . $case->name_animal . ' - ' . $case->case_type,
                [
                    'type' => 'immediate_case',
                    'case_id' => (string) $case->id,
                    'appointment_id' => (string) $appointment->id,
                    'animal_name' => $case->name_animal,
                    'case_type' => $case->case_type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]
            );
            
            return true;
        } catch (Throwable $e) {
            Log::error('فشل إرسال إشعار الحالة الفورية', [
                'employee_id' => $employee->id,
                'case_id' => $case->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * إرسال إشعار بتحديث حالة الموعد
     */
    public function sendAppointmentStatusNotification(Appointment $appointment, string $status): bool
    {
        try {
            $user = $appointment->user;
            
            if (!$user) {
                throw new \Exception("الموعد لا يحتوي على مستخدم مرتبط");
            }
            
            $user->notify(new AppointmentStatusNotification($appointment, $status));
            
            $statusText = $status === 'approved' ? 'قبول' : 'رفض';
            
            $this->sendFcmNotification(
                $user,
                'تحديث حالة الموعد',
                "تم {$statusText} موعدك رقم {$appointment->id}",
                [
                    'type' => 'appointment_status',
                    'appointment_id' => (string) $appointment->id,
                    'status' => $status,
                    'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]
            );
            
            return true;
        } catch (Throwable $e) {
            Log::error('فشل إرسال إشعار حالة الموعد', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * دالة مساعدة لإرسال إشعار FCM
     */
    protected function sendFcmNotification(?User $user, string $title, string $body, array $data = []): bool
    {
        if (!$user || !$user->fcm_token) {
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (Throwable $e) {
            Log::error('فشل إرسال إشعار FCM', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}