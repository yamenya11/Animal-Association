<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AnimalCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\sendAppointmentStatusNotification;
use App\Services\NotificationService;

class AppointmentService
{

 public function requestAppointment(Request $request)
{
    $validated = $request->validate([
        'animal_case_id' => 'required|exists:animal_cases,id',
        'scheduled_at'   => ['required', 'date', 'after:' . now()->addMinutes(5)],
        'doctor_id'      => 'nullable|exists:users,id',
    ]);

    // التحقق من وجود موعد مفتوح أو معتمد لنفس الحالة
    $existingAppointment = Appointment::where('animal_case_id', $validated['animal_case_id'])
        ->whereIn('status', ['pending', 'approved'])
        ->first();

    if ($existingAppointment) {
        return response()->json([
            'status'  => false,
            'message' => 'لا يمكن طلب موعد جديد لهذه الحالة لأن لديها موعد قيد المعالجة أو معتمد.',
        ], 422);
    }

    $appointmentData = [
        'user_id'        => Auth::id(),
        'animal_case_id' => $validated['animal_case_id'],
        'scheduled_at'   => $validated['scheduled_at'],
        'status'         => 'pending',
        
    ];

    // أضف doctor_id فقط إذا موجودة في الطلب (ليست null)
    if (!empty($validated['doctor_id'])) {
        $appointmentData['doctor_id'] = $validated['doctor_id'];
    }

    $appointment = Appointment::create($appointmentData);

    // تحميل بيانات الطبيب والمستخدم (الذي طلب الموعد)
    $appointment->load(['doctor:id,name,email', 'user:id,name,email']);

    return response()->json([
        'status'  => true,
        'message' => 'تم إرسال طلب الموعد، في انتظار الموافقة.',
        'data'    => $appointment,
    ]);
}

public function acceptappointmentImm( $appointment, string $action): array{
   $app = Appointment::with('user')->findOrFail($appointment);

       if (!in_array($action, ['approved', 'rejected'])) {
        return [
            'status' => false,
            'message' => 'إجراء غير صالح.',
        ];
    }

    $app->status = $action;
    $app->save();
    
    $notificationService = app(NotificationService::class);
    $notificationService->sendAppointmentStatusNotification($app, 'approved');



     return [
        'status' => true,
        'message' => $action === 'approved'
            ? 'تمت الموافقة على الموعد.'
            : 'تم رفض الموعد.',
        'data' => $app,
    ];

}

}