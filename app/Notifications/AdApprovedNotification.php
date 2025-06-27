<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\FCMService;
class AdApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;
   protected $ad;
    /**
     * Create a new notification instance.
     */
    public function __construct($ad)
    {
        $this->ad = $ad;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
         $channels = ['database'];
        
        // إرسال بريد إلكتروني فقط إذا كان لدى المستخدم عنوان بريد
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        // إرسال إشعار FCM فقط إذا كان هناك token
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
                    ->subject('تمت الموافقة على إعلانك - ' . config('app.name'))
                    ->greeting('مرحباً ' . $notifiable->name)
                    ->line('نود إعلامك أنه تمت الموافقة على إعلانك بعنوان:')
                   ->line('**' . $this->ad->title . '**')
                  ->action('عرض الإعلان', route('/ads', $this->ad->id))
                 ->line('شكراً لاستخدامك منصتنا.');
    }


     public function toFcm($notifiable)
    {
        return [
            'title' => 'تمت الموافقة على إعلانك',
            'body' => 'إعلانك "' . $this->ad->title . '" تمت الموافقة عليه',
            'data' => [
                'ad_id' => $this->ad->id,
                'type' => 'ad_approved',
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
    public function toArray(object $notifiable): array
    {
         return [
            'ad_id' => $this->ad->id,
            'title' => 'تمت الموافقة على إعلانك',
            'message' => 'تمت الموافقة على إعلانك: ' . $this->ad->title,
            'url' => url('/ads' . $this->ad->id),
            'icon' => asset('images/notification-icon.png')
        ];
    }
}
