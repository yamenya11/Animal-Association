<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;

class PostStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $status;

    public function __construct(Post $post, string $status)
    {
        $this->post = $post;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $statusText = $this->status === 'approved' ? 'تمت الموافقة على' : 'تم رفض';
        
        return (new MailMessage)
            ->subject('تحديث حالة المنشور')
            ->line("مرحباً {$notifiable->name}")
            ->line("{$statusText} منشورك: {$this->post->title}")
            ->line($this->post->notes ? "ملاحظات: {$this->post->notes}" : '')
            ->action('عرض المنشور', url('/posts/' . $this->post->id))
            ->line('شكراً لاستخدامك منصتنا');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'status' => $this->status,
            'notes' => $this->post->notes
        ];
    }
}
