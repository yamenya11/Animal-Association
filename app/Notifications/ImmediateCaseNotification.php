<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AnimalCase;
use App\Models\Appointment;

class ImmediateCaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $case;
    protected $appointment;

    public function __construct(AnimalCase $case, Appointment $appointment)
    {
        $this->case = $case;
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
       $channels = ['database'];
    
    if ($notifiable->fcm_token) {
        $channels[] = 'fcm';
    }
    
    return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('طلب فوري جديد - حالة طارئة')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('لقد تم تعيينك لمعالجة حالة طارئة جديدة:')
            ->line('اسم الحيوان: ' . $this->case->name_animal)
            ->line('نوع الحالة: ' . $this->case->case_type)
            ->line('الوقت المحدد: ' . $this->appointment->scheduled_at->format('Y-m-d H:i'))
            ->action('عرض التفاصيل', route('appointments.show', $this->appointment->id))
            ->line('شكراً لجهودك!');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'طلب فوري جديد',
            'body' => 'حالة طارئة لـ ' . $this->case->name_animal,
            'data' => [
                'type' => 'immediate_case',
                'case_id' => (string) $this->case->id,
                'appointment_id' => (string) $this->appointment->id,
                'animal_name' => $this->case->name_animal,
                'case_type' => $this->case->case_type,
                'scheduled_at' => $this->appointment->scheduled_at->toIso8601String(),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'immediate_case',
            'case_id' => $this->case->id,
            'appointment_id' => $this->appointment->id,
            'title' => 'طلب فوري جديد',
            'message' => 'حالة طارئة لـ ' . $this->case->name_animal,
            'animal_name' => $this->case->name_animal,
            'case_type' => $this->case->case_type,
            'scheduled_at' => $this->appointment->scheduled_at->toIso8601String(),
            //'url' => route('appointments.show', $this->appointment->id),
            'icon' => asset('images/emergency-icon.png')
        ];
    }
}