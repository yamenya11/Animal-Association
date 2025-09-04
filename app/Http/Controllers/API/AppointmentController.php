<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
         protected $appointmentService;

        public function __construct(AppointmentService $appointmentService)
        {
            $this->appointmentService = $appointmentService;
        }

        public function request(Request $request): JsonResponse
        {
            $appointment = $this->appointmentService->scheduleAppointment($request);

            return response()->json([
                'status' => true,
                'data' => $appointment,
            ]);
        }

        public function pending()
        {
        $appointments = $this->appointmentService->getPendingAppointments();
        return response()->json([
            'status' => true,
            'data' => $appointments
        ]);
        }

 public function update(Request $request): JsonResponse
        {
            $appointment = $this->appointmentService->updateAppointment($request);
            return response()->json([
                'status'=>true,
                'data'=>$appointment
            ],200);
        }

  public function showAppointmentMyUser()
    {
        $appointments = Appointment::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->select('id', 'status', 'scheduled_date','scheduled_time', 'is_immediate')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $appointments,
        ]);
    }


//   public function respond(Request $request, $app)
//     {
//         $request->validate([
//             'action' => 'required|in:completed,canceled',
//         ]);

//         $result = $this->appointmentService->acceptappointmentImm($app, $request->action);

//         return response()->json($result);
//     }


    public function getProcessedAppointments()
{
    // جلب المواعيد التي تمت الموافقة عليها أو رفضها فقط
    $appointments = Appointment::whereIn('status', ['scheduled', 'canceled'])
                              ->with(['user','animalCase', 'ambulance'])
                              ->get();

    return response()->json([
        'status' => true,
        'data' => $appointments
    ]);
}

    public function getAppointmentsByStatus($status)
{
    if (!in_array($status, ['completed', 'canceled'])) {
        return response()->json([
            'status' => false,
            'message' => 'حالة غير صالحة. يرجى استخدام "completed" أو "canceled"'
        ], 400);
    }

    $appointments = Appointment::where('status', $status)
                              ->with(['user', 'animalCase', 'ambulance'])
                              ->get();

    return response()->json([
        'status' => true,
        'data' => $appointments
    ]);
}

}
