<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class VolunteerRequestApproved extends Notification
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
        return ['mail'];
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
        ->action('عرض الطلب', url('/volunteer/requests/' . $this->volunteerRequest->id))
        ->line('شكراً لانضمامك معنا ونتمنى لك تجربة تطوعية مفيدة.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
