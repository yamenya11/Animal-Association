<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment; // أضف هذا الاستيراد في الأعلى

class AppointmentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

     protected $appointment;
    protected $status;

    public function __construct(Appointment $appointment, string $status)
    {
        $this->appointment = $appointment;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
      $channels = ['database'];
    
    if ($notifiable->fcm_token) {
        $channels[] = 'fcm';
    }
    
    return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تحديث حالة الموعد - راجع البريد الوارد')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم ' . ($this->status === 'approved' ? 'قبول' : 'رفض') . ' موعدك.')
            ->line('رقم الموعد: ' . $this->appointment->id)
            ->line('الحالة الجديدة: ' . $this->status);
    }

       public function toFcm($notifiable)
    {
        $statusMessage = $this->getStatusMessage();

         return [
            'title' => 'تحديث حالة الموعد',
            'body' => 'تم ' . ($this->status === 'approved' ? 'قبول' : 'رفض') . ' موعدك رقم ' . $this->appointment->id,
            'data' => [
                'type' => 'appointment_status',
                'appointment_id' => (string) $this->appointment->id,
                'status' => $this->status,
                'scheduled_at' => $this->appointment->scheduled_at->toIso8601String(),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
         return [
            'appointment_id' => $this->appointment->id,
            'status' => $this->status,
            'message' => 'تم ' . ($this->status === 'approved' ? 'قبول' : 'رفض') . ' موعدك.',
        ];
    }
}
