<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

    $notifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->through(function ($notification) {
            return $this->formatNotification($notification);
        });

    return response()->json([
        'status' => true,
        'notifications' => $notifications,
        'unread_count' => $user->unreadNotifications->count(),
    ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);
            
        $notification->markAsRead();
        
        return response()->json([
            'status' => true,
            'message' => 'تم تعيين الإشعار كمقروء',
            'notification' => $this->formatNotification($notification)
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $count = $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);
            
        return response()->json([
            'status' => true,
            'message' => "تم تعيين {$count} إشعارات كمقروءة"
        ]);
    }

  protected function formatNotification(DatabaseNotification $notification)
{
    $data = is_array($notification->data)
        ? $notification->data
        : json_decode($notification->data, true) ?? [];

    return [
        'id' => $notification->id,
        'type' => $data['type'] ?? 'general',
        'title' => $this->getNotificationTitle($data),
        'body' => $data['message'] ?? '',
        'is_read' => !is_null($notification->read_at),
        'created_at' => $notification->created_at->toIso8601String(),
    ];
}


    protected function normalizeNotificationData($data): array
    {
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }
        
        return (array)$data;
    }

    protected function getNotificationTitle(array $data): string
    {
        $type = $data['type'] ?? '';
        $status = $data['status'] ?? '';
        
        $titles = [
            'post_status_update' => $status === 'approved' ? 
                'تمت الموافقة على منشورك' : 'تم رفض منشورك',
            'ad_approved' => 'تمت الموافقة على إعلانك',
            'ad_rejected' => 'تم رفض إعلانك',
            'adoption_approved' => 'تمت الموافقة على طلب التبني',
            'adoption_rejected' => 'تم رفض طلب التبني',
            'donation_status' => 'تحديث حالة التبرع',
            'immediate_case' => 'حالة طارئة جديدة'
        ];
        
        return $titles[$type] ?? 'إشعار جديد';
    }

    protected function getDeepLink(array $data): ?string
    {
        $type = $data['type'] ?? '';
        $id = $data['id'] ?? $data[$type.'_id'] ?? null;
        
        if (!$id) {
            return null;
        }
        
        $links = [
            'post_status_update' => 'app://posts/',
            'ad_approved' => 'app://ads/',
            'ad_rejected' => 'app://ads/',
            'adoption_approved' => 'app://adoptions/',
            'adoption_rejected' => 'app://adoptions/',
            'donation_status' => 'app://donations/',
            'immediate_case' => 'app://cases/'
        ];
        
        return isset($links[$type]) ? $links[$type].$id : null;
    }
}