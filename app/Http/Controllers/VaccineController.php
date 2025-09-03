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
        'animal_id' => 'required|exists:animals,id',
        'gender' => 'required|in:ذكر,أنثى,male,female',
        'type' => 'required|string',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'due_date' => 'required|date|after_or_equal:today',
    ]);

    try {
        $result = $this->vaccineService->create($validated);
        return response()->json($result, 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل في إضافة اللقاح',
            'error' => $e->getMessage()
        ], 500);
    }
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

     // يمكنك إضافة دالة جديدة لتحديث الصورة
    public function updateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $vaccine = $this->vaccineService->updateImage($request, $id);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث صورة اللقاح بنجاح',
            'data' => $vaccine
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

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'animal_id' => 'sometimes|exists:animals,id',
        'gender' => 'sometimes|in:male,female',
        'type' => 'sometimes|string',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        'due_date' => 'sometimes|date|after_or_equal:today',
    ]);

    try {
        $vaccine = $this->vaccineService->update($validated, $id);
        
        return response()->json([
            'status' => true,
            'message' => 'تم تحديث اللقاح بنجاح',
            'data' => $vaccine
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل في تحديث اللقاح',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        $this->vaccineService->delete($id);
        
        return response()->json([
            'status' => true,
            'message' => 'تم حذف اللقاح بنجاح'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل في حذف اللقاح',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function show($id)
{
    try {
        $vaccine = $this->vaccineService->show($id);
        
        return response()->json([
            'status' => true,
            'data' => $vaccine
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'اللقاح غير موجود',
            'error' => $e->getMessage()
        ], 404);
    }
}

}

