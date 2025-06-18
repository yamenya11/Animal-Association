<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\Channels\NotificationChannel;
use Illuminate\Support\Facades\Notification;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
        Notification::extend('fcm', function ($app) {
            return new class($app->make(FCMService::class)) implements NotificationChannel {
                protected $fcmService;

                public function __construct(FCMService $fcmService)
                {
                    $this->fcmService = $fcmService;
                }

                public function send($notifiable, Notification $notification)
                {
                    if (method_exists($notification, 'toFcm')) {
                        $data = $notification->toFcm($notifiable);
                        $this->fcmService->sendToToken($notifiable->fcm_token, $data);
                    }
                }
            };
        });
    //
    }
}
