<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdobtStatusAccept extends Notification
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
        return ['mail', 'database'];
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
        ->line('شكراً لانضمامك معنا ونتمنى لك تجربة مفيدة.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'adoption_id' => $this->adoptRequest->id ?? null,
            'message' => 'تمت الموافقة على طلب التبني الخاص بك',
        ];
    }
}
