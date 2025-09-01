<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnimalCase;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AppointmentService;
class StafController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }


    //عرض الحالات العادية
  public function listRegularCases()
{
    $cases = AnimalCase::where('request_type', 'regular')
                ->with('appointments') // إذا أردت عرض معلومات الموعد أيضاً
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json([
        'status' => true,
        'message' => 'قائمة الحالات العادية',
        'data' => $cases,
    ]);
}

public function listImmediateCases()
{
    $appointments = Appointment::where('is_immediate', true)
        ->with(['animalCase.user']) // نحمل فقط بيانات الحالة والمستخدم
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($appointment) {
            $case = $appointment->animalCase;
            $user = $case->user;
            
            return [
                'appointment_id' => $appointment->id,
                'animal_info' => [
                    'name' => $case->name_animal,
                    'case_type' => $case->case_type,
                    'description' => $case->description,
                    'image_url' => $case->image ? asset('storage/' . $case->image) : null,
                ],
                'emergency_contact' => [
                    'phone' => $case->emergency_phone,
                    'address' => $case->emergency_address
                ],
                'reporter' => [
                    'name' => $user->name,
                    'phone' => $user->phone ?? $case->emergency_phone
                ],
                'appointment_details' => [
                    'created_at' => $appointment->created_at->diffForHumans(),
                    'scheduled_time' => $appointment->scheduled_time,
                    'status' => $appointment->status
                ]
            ];
        });

    return [
        'status' => true,
        'message' => 'قائمة المواعيد الفورية المعلقة',
        'data' => $appointments,
    ];

}
/////عرض المواعيد
public function getImmediateAppointments()
{
    return Appointment::with(['user', 'animalCase'])
        ->where('is_immediate', true)
        ->where('status', 'pending')
        ->orderBy('scheduled_at', 'asc')
        ->get();
}

public function availableDoctors()
{
    $doctors = User::role('vet')->get(); // باستخدام Spatie

    return response()->json([
        'status' => true,
        'message' => 'قائمة الأطباء',
        'data' => $doctors
    ]);
}

 public function scheduleImmediate(Request $request, $caseId)
    {
        $response = $this->appointmentService->scheduleImmediateAppointment($request, $caseId);

        return response()->json($response, $response['status'] ? 201 : 400);
    }
}
