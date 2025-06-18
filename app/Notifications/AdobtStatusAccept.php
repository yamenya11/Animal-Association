<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
class AdobtStatusAccept extends Notification implements ShouldQueue
{
    use Queueable;

    protected $adoptRequest;


    public function __construct($adoptRequest)
    {
        $this->adoptRequest=$adoptRequest;
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        
           $channels = ['database'];
        
        // إضافة البريد الإلكتروني إذا كان موجوداً
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        // إضافة FCM إذا كان هناك token
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
            ->subject('تمت الموافقة على طلب التبني')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('نود إعلامك أنه تمت الموافقة على طلب التبني الخاص بك.')
            ->action('عرض التفاصيل', route('adoptions.show', $this->adoptRequest->id))
            ->line('شكراً لانضمامك معنا ونتمنى لك تجربة مفيدة.');
    }


      public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'حالة طلب التبني',
            'body' => 'تمت الموافقة على طلب التبني الخاص بك',
            'data' => [
                'adoption_id' => $this->adoptRequest->id,
                'type' => 'adoption_approved',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
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
            'adoption_id' => $this->adoptRequest->id,
            'message' => 'تمت الموافقة على طلب التبني الخاص بك',
            'url' => route('adoptions.show', $this->adoptRequest->id),
            'icon' => asset('images/notification-icon.png')
        ];
    }
}
