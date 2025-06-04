<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
class AppointmentController extends Controller
{
     protected $appointmentService;

      public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

       public function request(Request $request): JsonResponse
    {
        $appointment = $this->appointmentService->requestAppointment($request);

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

}
