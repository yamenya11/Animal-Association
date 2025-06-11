<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;
class PostStatusUpdated extends Notification
{
    use Queueable;

   protected $post;
    protected $action;
    public function __construct(Post $post, string $action)
    {
         $this->post = $post;
        $this->action = $action;
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
                    ->subject('تم قبول منشورك')
                    ->greeting('مرحباً ' . $notifiable->name)
                  ->line('تم ' . ($this->action === 'approved' ? 'الموافقة' : 'رفض') . ' منشورك المعنون بـ: "' . $this->post->title . '"')
                ->line('شكراً لاستخدامك منصتنا!');
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
