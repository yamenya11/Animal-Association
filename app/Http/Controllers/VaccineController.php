<?php


// app/Http/Controllers/VaccineController.php
namespace App\Http\Controllers;

use App\Services\VaccineService;
use Illuminate\Http\Request;

class VaccineController extends Controller
{
    protected $vaccineService;

    public function __construct(VaccineService $vaccineService)
    {
        $this->vaccineService = $vaccineService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_name' => 'required|string|min:2',
            'type'        => 'required|string',
            'due_date'    => 'required|date|after_or_equal:today',
        ]);

        $vaccine = $this->vaccineService->create($request);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة اللقاح بنجاح',
            'data' => $vaccine
        ]);
    }

    public function index()
    {
        $vaccines = $this->vaccineService->list();

        return response()->json([
            'status' => true,
            'data' => $vaccines
        ]);
    }
      // عرض جميع الإشعارات للمستخدم الحالي
    public function notifications()
    {
        $notifications = auth()->user()->notifications;

        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    // عرض الإشعارات الغير مقروءة فقط
    public function unreadNotifications()
    {
        $notifications = auth()->user()->unreadNotifications;

        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    // تعليم كل الإشعارات كمقروءة
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'تم تعليم كل الإشعارات كمقروءة',
        ]);
    }

    public function markAsReadById($id)
{
    $notification = auth()->user()->notifications()->find($id);

    if (!$notification) {
        return response()->json([
            'status' => false,
            'message' => 'الإشعار غير موجود'
        ], 404);
    }

    if (is_null($notification->read_at)) {
        $notification->markAsRead();
    }

    return response()->json([
        'status' => true,
        'message' => 'تم تعليم الإشعار كمقروء',
        'notification_id' => $notification->id
    ]);
}

}

