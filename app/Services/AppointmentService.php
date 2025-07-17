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
public function acceptappointmentImm($appointment, string $action): array
{
    $app = Appointment::with('user', 'animalCase')->findOrFail($appointment);

    if (!in_array($action, ['completed', 'canceled'])) {
        return [
            'status' => false,
            'message' => 'إجراء غير صالح.',
        ];
    }

    if ($action === 'completed') {
        // 1. تحقق مما إذا كان هناك سيارة إسعاف مرتبطة بالفعل
        if (!$app->ambulance_id) {
            // 2. إذا لم يكن هناك سيارة إسعاف، إنشاء واحدة وهمية أو استخدام واحدة متاحة
            $ambulance = Ambulance::where('status', 'available')->first();

            // 3. إذا لم توجد سيارات إسعاف متاحة، إنشاء واحدة وهمية
            if (!$ambulance) {
                $ambulance = Ambulance::create([
                   
                    'driver_name' => 'سائق افتراضي',
                    'driver_phone' => '0599' . rand(1000000, 9999999), // رقم وهمي
                    'status' => 'on_mission',
                ]);
            } else {
                $ambulance->update(['status' => 'on_mission']);
            }

            // 4. ربط سيارة الإسعاف بالموعد
            $app->ambulance_id = $ambulance->id;
            $app->description = 'طلب طارئ - سيارة إسعاف: ' . $ambulance->plate_number;
        }

        $app->status = 'completed';
        $app->save();

        // (اختياري) إرسال إشعار وهمي
        // $this->dispatchAmbulance(
        //     $app->animalCase->emergency_address,
        //     $app->animalCase->emergency_phone,
        //     $ambulance
        // );
    } else {
        $app->status = 'canceled';
        $app->save();
    }

    return [
        'status' => true,
        'message' => $action === 'completed'
            ? 'تمت الموافقة على الموعد وربط سيارة إسعاف.'
            : 'تم رفض الموعد.',
        'data' => $app->load('ambulance'), // تضمين بيانات سيارة الإسعاف في الاستجابة
    ];
}

}