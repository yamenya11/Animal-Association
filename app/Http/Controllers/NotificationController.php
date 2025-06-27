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
            ->paginate(15) // الترقيم مهم لتطبيقات الموبايل
            ->through(function ($notification) {
                return $this->formatNotification($notification);
            });
            
        return response()->json([
            'status' => true,
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications->count()
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
        $request->user()
            ->unreadNotifications
            ->markAsRead();
            
        return response()->json([
            'status' => true,
            'message' => 'تم تعيين جميع الإشعارات كمقروءة'
        ]);
    }

    protected function formatNotification(DatabaseNotification $notification)
    {
        $data = $notification->data;
        
        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'general',
            'title' => $this->getNotificationTitle($data),
            'body' => $data['message'] ?? '',
            'is_read' => $notification->read_at !== null,
            'created_at' => $notification->created_at->toIso8601String(),
            'data' => $data,
            'deep_link' => $this->getDeepLink($data) // روابط عميقة للتطبيق
        ];
    }

    protected function getNotificationTitle(array $data): string
    {
        switch ($data['type'] ?? '') {
            case 'post_status_update':
                return $data['status'] === 'approved' 
                    ? 'تمت الموافقة على منشورك' 
                    : 'تم رفض منشورك';
            case 'adoption_approved':
                return 'حالة طلب التبني';
            case 'donation_status':
                return 'تحديث حالة التبرع';
            default:
                return 'إشعار جديد';
        }
    }

    protected function getDeepLink(array $data): ?string
    {
        switch ($data['type'] ?? '') {
            case 'post_status_update':
                return 'app://posts/'.$data['post_id'];
            case 'adoption_approved':
                return 'app://adoptions/'.$data['adoption_id'];
            case 'donation_status':
                return 'app://donations/'.$data['donation_id'];
            default:
                return null;
        }
    }
}