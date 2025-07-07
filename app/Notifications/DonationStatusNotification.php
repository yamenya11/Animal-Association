<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Donate; // أضف هذا الاستيراد في الأعلى
class DonationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

     protected $donation;
    protected $status;
    /**
     * Create a new notification instance.
     */
        public function __construct(Donate $donation, string $status)
    {
        $this->donation = $donation;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
      $channels = ['database'];
        
       $channels = ['database'];
    
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
      $statusMessage = $this->getStatusMessage();

        return (new MailMessage)
            ->subject('تحديث حالة التبرع')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line($statusMessage)
            ->line('مبلغ التبرع: ' . $this->donation->amount . ' ' . $this->donation->currency)
            ->action('عرض التفاصيل', route('donations.show', $this->donation->id))
            ->line('شكراً لدعمكم ومساهمتكم في إنجاح رسالتنا');
    }

      public function toFcm($notifiable)
    {
        $statusMessage = $this->getStatusMessage();

        return [
            'title' => 'حالة التبرع',
            'body' => $statusMessage,
            'data' => [
                'donation_id' => $this->donation->id,
                'type' => 'donation_status',
                'status' => $this->status,
                'amount' => $this->donation->amount,
                'currency' => $this->donation->currency,
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
            'donation_id' => $this->donation->id,
            'message' => $this->getStatusMessage(),
            'amount' => $this->donation->amount,
            'currency' => $this->donation->currency,
            'url' => route('donations.show', $this->donation->id),
            'icon' => asset('images/donation-icon.png')
        ];
    }

      protected function getStatusMessage(): string
    {
        return match($this->status) {
            'completed' => 'تم استلام تبرعك بنجاح',
            'pending' => 'جاري معالجة تبرعك',
            'failed' => 'فشل في معالجة التبرع',
            default => 'تحديث على حالة التبرع'
        };
    }
}
