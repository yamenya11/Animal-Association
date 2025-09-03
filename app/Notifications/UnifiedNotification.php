<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected object $model; // النموذج المرتبط (Adoption, Donate, Post, AnimalCase)
    protected string $type;   // نوع الإشعار: adopt, donation, post, immediate_case
    protected string $status; // الحالة: approved, rejected, completed, pending, etc.
    protected ?string $customMessage; // رسالة مخصصة إذا أردنا تجاوز الرسالة الافتراضية

    public function __construct(object $model, string $type, string $status, ?string $customMessage = null)
    {
        $this->model = $model;
        $this->type = $type;
        $this->status = $status;
        $this->customMessage = $customMessage;
    }

    /**
     * قنوات الإشعار
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->fcm_token) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    /**
     * البريد الإلكتروني
     */
    public function toMail($notifiable)
    {
        $message = $this->customMessage ?? $this->getStatusMessage();

        return (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('مرحباً ' . $notifiable->name)
            ->line($message)
            ->action('عرض التفاصيل', $this->getUrl())
            ->line('شكراً لاستخدامك منصتنا!');
    }

    /**
     * FCM
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => $this->getSubject(),
            'body' => $this->customMessage ?? $this->getStatusMessage(),
            'data' => $this->getFcmData()
        ];
    }

    /**
     * قاعدة البيانات
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'status' => $this->status,
            'message' => $this->customMessage ?? $this->getStatusMessage(),
            'model_id' => $this->model->id ?? null,
            'extra' => $this->getExtraData(),
        ];
    }

    // ===========================
    // دوال مساعدة
    // ===========================

    protected function getSubject(): string
    {
        return match($this->type) {
            'adopt' => 'حالة طلب التبني',
            'donation' => 'حالة التبرع',
            'post' => 'حالة المنشور',
            'immediate_case' => 'طلب فوري جديد',
            default => 'إشعار جديد',
        };
    }

    protected function getStatusMessage(): string
    {
        return match($this->type) {
            'adopt' => $this->status === 'approved' 
                ? 'تمت الموافقة على طلب التبني.' 
                : 'تم رفض طلب التبني.',
            'donation' => $this->status === 'approved' 
                ? 'تمت الموافقة على التبرع، يمكنك زيارة الجمعية لتقديم الدعم.' 
                : 'تم رفض التبرع.',
            'post' => $this->status === 'approved' 
                ? 'تمت الموافقة على منشورك.' 
                : 'تم رفض منشورك.',
            'immediate_case' => 'تم تعيينك لمعالجة حالة طارئة جديدة: ' . ($this->model->name_animal ?? ''),
            default => 'تم تحديث حالتك.'
        };
    }

    protected function getUrl(): string
    {
        return match($this->type) {
            'adopt' => url('/adoptions/' . $this->model->id),
            'donation' => url('/donations/' . $this->model->id),
            'post' => url('/posts/' . $this->model->id),
            'immediate_case' => url('/appointments/' . ($this->model->appointment_id ?? '')),
            default => url('/'),
        };
    }

    protected function getFcmData(): array
    {
        return [
            'type' => $this->type,
            'status' => $this->status,
            'model_id' => $this->model->id ?? null,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
    }

    protected function getExtraData(): array
    {
        switch ($this->type) {
            case 'donation':
                return [
                    'amount' => $this->model->amount ?? null,
                    'currency' => $this->model->currency ?? null
                ];
            case 'immediate_case':
                return [
                    'animal_name' => $this->model->name_animal ?? '',
                    'case_type' => $this->model->case_type ?? '',
                    'appointment_id' => $this->model->appointment_id ?? null
                ];
            default:
                return [];
        }
    }
}
