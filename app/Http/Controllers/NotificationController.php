<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
        // جلب جميع إشعارات المستخدم
    public function index(Request $request)
    {
    $user = $request->user();

    $notifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? '',
              //  'post_id' => $notification->data['post_id'] ?? null,
             //   'title' => $notification->data['title'] ?? null,
             //   'action' => $notification->data['action'] ?? null,
              //  'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

    return response()->json([
        'status' => true,
        'notifications' => $notifications,
    ]);
    }

      public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['status' => true, 'message' => 'تم تعيين الإشعار كمقروء']);
    }
}
