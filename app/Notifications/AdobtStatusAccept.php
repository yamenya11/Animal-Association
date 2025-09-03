<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdobtStatusAccept extends Notification implements ShouldQueue
{
    use Queueable;

    protected $adoptRequest;

    public function __construct($adoptRequest)
    {
        $this->adoptRequest = $adoptRequest;
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
        $animalType = optional(optional($this->adoptRequest)->animal)->type ?? 'غير معروف';

        return (new MailMessage)
            ->subject('حالة طلب التبني')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تمت ' . ($this->adoptRequest->status === 'approved' ? 'الموافقة' : 'رفض') . ' طلب التبني الخاص بك.')
            ->line('نوع الحيوان: ' . $animalType)
            ->line('شكراً لاهتمامك بالتبني!');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'حالة طلب التبني',
            'body'  => 'تمت ' . ($this->adoptRequest->status === 'approved' ? 'الموافقة' : 'رفض') . ' طلب التبني الخاص بك',
            'data'  => [
                'adoption_id'  => (string) $this->adoptRequest->id,
                'type'         => 'adoption_' . $this->adoptRequest->status,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]
        ];
    }

 public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'adoption_' . $this->adoptRequest->status,
            'adoption_id' => $this->adoptRequest->id,
            'message'     => 'تمت ' . ($this->adoptRequest->status === 'approved' ? 'الموافقة' : 'رفض') . ' طلب التبني الخاص بك',
            'status'      => $this->adoptRequest->status,
        ];
    }
}















