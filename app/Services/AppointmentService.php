<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AnimalCase;
use App\Models\User;
use App\Models\Ambulance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\sendAppointmentStatusNotification;
use App\Services\NotificationService;

class AppointmentService
{


public function scheduleAppointment(Request $request)
{
     $validated = $request->validate([
         'animal_case_id'  => 'required|exists:animal_cases,id',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'scheduled_time' => 'required|date_format:H:i',
        'description'    => 'required|string|min:10'
    ]);

    // البحث عن الحالة باستخدام اسم الحيوان
$animalCase = AnimalCase::find($validated['animal_case_id']);
    // التحقق من عدم وجود موعد سابق
    if ($animalCase->appointments()->where('status', 'scheduled')->exists()) {
        return response()->json([
            'status' => false,
            'message' => 'هذه الحالة لديها موعد مجدول بالفعل'
        ], 422);
    }

    $appointment = Appointment::create([
        'user_id' => $animalCase->user_id,
       // 'doctor_id' => auth()->id(), // الطبيب الحالي
        'animal_case_id' => $animalCase->id,
        'scheduled_date' => $validated['scheduled_date'],
        'scheduled_time' => $validated['scheduled_time'],
        'description' => $validated['description'],
        'status' => 'scheduled'
    ]);

    // إرسال إشعار للمستخدم
    //$animalCase->user->notify(new AppointmentScheduled($appointment));

  return response()->json([
    'status' => true,
    'message' => 'تم جدولة الموعد بنجاح',
    'appointment' => $appointment->load('animalCase:id,name_animal,case_type,image'),
    'doctor' => auth()->user()->only(['id', 'name']),
]);
}
// public function acceptappointmentImm($appointment, string $action): array
// {
//     $app = Appointment::with('user', 'animalCase')->findOrFail($appointment);

//     if (!in_array($action, ['completed', 'canceled'])) {
//         return [
//             'status' => false,
//             'message' => 'إجراء غير صالح. يجب أن يكون "completed" أو "canceled"',
//         ];
//     }

//     $app->status = $action;

//     if ($action === 'completed') {
//         // معالجة المواعيد الطارئة
//         if ($app->is_immediate && !$app->ambulance_id) {
//             $ambulance = Ambulance::firstOrCreate(
//                 ['status' => 'available'],
//                 [
//                     'driver_name' => 'سائق افتراضي',
//                     'driver_phone' => '0599' . rand(1000000, 9999999),
//                     'status' => 'on_mission'
//                 ]
//             );
//             $app->ambulance_id = $ambulance->id;
//         }
        
//         // تحديث وصف الموعد
//         $app->description = $app->is_immediate 
//             ? 'تم إكمال الموعد الطارئ'
//             : 'تمت الموافقة وإكمال الموعد';
//     }

//     $app->save();

//     // تحديث حالة الحالة الحيوانية المرتبطة
//     if ($app->animalCase) {
//         $app->animalCase->update([
//             'approval_status' => $action === 'completed' ? 'approved' : 'rejected'
//         ]);
//     }

//     return [
//         'status' => true,
//         'message' => $action === 'completed' 
//             ? 'تمت الموافقة وإكمال الموعد بنجاح'
//             : 'تم إلغاء الموعد',
//         'data' => $app->load(['ambulance', 'animalCase'])
//     ];
// }

}