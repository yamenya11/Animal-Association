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
     */
    public function via($notifiable)
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
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('تحديث حالة المنشور - Animal App')
        ->greeting('مرحباً ' . $notifiable->name)
        ->line('حالة منشورك: **' . $this->post->title . '**')
        ->line('**النتيجة:** ' . ($this->status === 'approved' ? 'مقبول ✅' : 'مرفوض ❌'))
        ->action('عرض المنشور', url('/posts/' . $this->post->id))  # رابط مباشر للمنشور
        ->line('شكراً لاستخدامك Animal App!')
        ->salutation('مع تحيات، فريق Animal App');
    }

    /**
     * Get the FCM notification representation.
     */
    public function toFcm(object $notifiable): array
    {
        $statusMessage = $this->status === 'approved' 
            ? 'تمت الموافقة على منشورك' 
            : 'تم رفض منشورك';

        return [
            'title' => 'تحديث حالة المنشور',
            'body' => $statusMessage . ': ' . $this->post->title,
            'data' => [
                'post_id' => $this->post->id,
                'type' => 'post_status_update',
                'status' => $this->status,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'title' => $this->post->title,
            'status' => $this->status,
            'message' => 'تم ' . ($this->status === 'approved' ? 'الموافقة' : 'رفض') . ' منشورك',
        ];
    }
}
