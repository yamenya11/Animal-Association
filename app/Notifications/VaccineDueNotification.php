<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VaccineDueNotification extends Notification
{
    use Queueable;

    public $vaccine;
      public function __construct($vaccine)
    {
        $this->vaccine = $vaccine;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
  public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
public function toArray($notifiable)
{
    return [
        'title' => 'موعد لقاح اليوم',
        'message' => 'اليوم موعد لقاح ' . $this->vaccine->type .
                    ' للحيوان: ' . ($this->vaccine->animal->name ?? 'غير معروف'),
        'due_date' => $this->vaccine->due_date,
    ];
}


}
