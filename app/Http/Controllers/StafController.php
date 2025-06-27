<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnimalCase;
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


//عرض الحالات الفورية
public function listImmediateCases()
{
    $cases = AnimalCase::where('request_type', 'immediate')
                ->with('appointments')
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json([
        'status' => true,
        'message' => 'قائمة الحالات الفورية',
        'data' => $cases,
    ]);
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
