<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
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

    public function toFcm($notifiable)
    {
        return [
            'title' => 'تم تحديد موعد جديد',
            'body'  => 'موعدك رقم ' . $this->appointment->id . 
                       ' بتاريخ ' . $this->appointment->scheduled_at->format('Y-m-d H:i'),
            'data'  => [
                'type' => 'appointment_scheduled',
                'appointment_id' => (string) $this->appointment->id,
                'scheduled_at'   => $this->appointment->scheduled_at->toIso8601String(),
                'click_action'   => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'scheduled_at'   => $this->appointment->scheduled_at->toDateTimeString(),
            'message'        => 'تم تحديد موعد جديد بتاريخ ' . $this->appointment->scheduled_at->format('Y-m-d H:i'),
        ];
    }
}
