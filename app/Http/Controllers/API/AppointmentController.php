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


//    public function handleImmediateCaseDecision(Request $request, Appointment $appointment)
// {
//     $user = Auth::user();

//     if (!$user->hasRole(['employee', 'doctor'])) {
//         return response()->json([
//             'status' => false,
//             'message' => 'غير مصرح لك بتولي هذه الحالة.',
//         ], 403);
//     }

//     if ($appointment->employee_id || $appointment->doctor_id) {
//         // الحالة تم توليها مسبقًا

//         // تحقق هل هذا المستخدم هو من تولى الحالة
//         if (($user->hasRole('employee') && $appointment->employee_id !== $user->id) ||
//             ($user->hasRole('doctor') && $appointment->doctor_id !== $user->id)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'لا يمكنك اتخاذ قرار على حالة لم تتولها.',
//             ], 403);
//         }

//         // الآن ننتظر قرار المستخدم: قبول أو رفض
//         $request->validate([
//             'action' => 'required|in:accept,reject',
//         ]);

//         if ($request->action === 'accept') {
//             $appointment->status = 'approved';
//             $message = 'تم قبول الحالة بنجاح.';
//         } else {
//             $appointment->status = 'rejected';
//             $message = 'تم رفض الحالة بنجاح.';
//         }

//         $appointment->save();

//         return response()->json([
//             'status' => true,
//             'message' => $message,
//             'data' => $appointment,
//         ]);

//     } else {
//         // تعيين الموظف أو الطبيب إذا لم يتم التعيين بعد
//         if ($user->hasRole('employee')) {
//             $appointment->employee_id = $user->id;
//         } elseif ($user->hasRole('doctor')) {
//             $appointment->doctor_id = $user->id;
//         }

//         $appointment->status = 'pending'; // لا تقبل أو ترفض بعد التعيين

//         $appointment->save();

//         return response()->json([
//             'status' => true,
//             'message' => 'تم تولي الحالة، يمكنك قبولها أو رفضها لاحقاً.',
//             'data' => $appointment,
//         ]);
//     }
// }

  public function showAppointmentMyUser()
    {
        $appointments = Appointment::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->select('id', 'status', 'scheduled_at', 'is_immediate')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $appointments,
        ]);
    }


  public function respond(Request $request, $app)
    {
        $request->validate([
            'action' => 'required|in:completed,canceled',
        ]);

        $result = $this->appointmentService->acceptappointmentImm($app, $request->action);

        return response()->json($result);
    }


    public function getProcessedAppointments()
{
    // جلب المواعيد التي تمت الموافقة عليها أو رفضها فقط
    $appointments = Appointment::whereIn('status', ['completed', 'canceled'])
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
