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
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('تم قبول منشورك')
                    ->greeting('مرحباً ' . $notifiable->name)
                  ->line('تم ' . ($this->action === 'approved' ? 'الموافقة' : 'رفض') . ' منشورك المعنون بـ: "' . $this->post->title . '"')
                ->line('شكراً لاستخدامك منصتنا!');
    }


    

    public function toFcm(object $notifiable): array
    {
        $statusMessage = $this->action === 'approved' 
            ? 'تمت الموافقة على منشورك' 
            : 'تم رفض منشورك';

        return [
            'title' => 'تحديث حالة المنشور',
            'body' => $statusMessage . ': ' . $this->post->title,
            'data' => [
                'post_id' => $this->post->id,
                'type' => 'post_status_update',
                'status' => $this->action,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
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
            'post_id' => $this->post->id,
            'title' => $this->post->title,
            'action' => $this->action,
            'message' => 'تم ' . ($this->action === 'approved' ? 'الموافقة' : 'رفض') . ' منشورك',
        ];
    }
}
