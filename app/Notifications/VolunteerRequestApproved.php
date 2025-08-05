<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class VolunteerRequestApproved extends Notification  implements ShouldQueue
{
    use Queueable;

    
 protected $volunteerRequest;

   
    public function __construct($volunteerRequest)
    {
      $this->volunteerRequest = $volunteerRequest;
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
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('تمت الموافقة على طلب التطوع')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('نود إعلامك أنه تمت الموافقة على طلب التطوع الخاص بك.')
            ->line('شكراً لانضمامك معنا ونتمنى لك تجربة تطوعية مفيدة.');
    }

      
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'طلب التطوع',
            'body' => 'تمت الموافقة على طلب التطوع الخاص بك!',
            'data' => [
                'volunteer_request_id' => $this->volunteerRequest->id ?? null,
                'type' => 'volunteer_approved',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ],
            'android' => [
                'priority' => 'high'
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10'
                ]
            ]
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'volunteer_request_id' => $this->volunteerRequest->id ?? null,
            'message' => 'تمت الموافقة على طلب التطوع الخاص بك',
        ];
    }
}
