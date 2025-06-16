<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AnimalCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AppointmentService
{

     public function requestAppointment(Request $request)
    {

          $validated = $request->validate([
            'animal_case_id' => 'required|exists:animal_cases,id',
            'scheduled_at' => 'required|date|after:now' .now()->addMinutes(5),
        ]);

          $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'animal_case_id' => $validated['animal_case_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'pending',
        ]);

         return [
            'status' => true,
            'message' => 'تم إرسال طلب الموعد، في انتظار الموافقة.',
            'data' => $appointment,
        ];
    }

    // public function getPendingAppointments()
    // {
    // return Appointment::select([
    //         'appointments.id',
    //         'appointments.status',
    //         'appointments.scheduled_at',
    //         'users.name as name_usre',
    //         'users.email as email_user',
    //         'animal_cases.case_type',
    //         'animal_cases.description',
    //         'animals.name as animal_name',
    //         'animals.type as animal_type',
    //     ])
    //     ->leftJoin('users', 'appointments.user_id', '=', 'users.id')
    //     ->leftJoin('animal_cases', 'appointments.animal_case_id', '=', 'animal_cases.id')
    //     ->leftJoin('animals', 'animal_cases.animal_id', '=', 'animals.id')
    //     ->where('appointments.status', 'pending')
    //     ->orderBy('appointments.scheduled_at', 'asc')
    //     ->get();
    // }

}