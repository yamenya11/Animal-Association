<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Message;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->fcm_token) {
            $channels[] = 'fcm';
        }
        return $channels;
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'رسالة جديدة في المحادثة',
            'body' => $this->message->body ?: '📎 رسالة تحتوي مرفق',
            'data' => [
                'type' => 'chat_message',
                'conversation_id' => (string) $this->message->conversation_id,
                'message_id' => (string) $this->message->id,
                'sender_id' => (string) $this->message->user_id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'message_id'      => $this->message->id,
            'sender_id'       => $this->message->user_id,
            'body'            => $this->message->body,
            'type'            => $this->message->type,
        ];
    }
}
