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
use App\Notifications\AppointmentStatusNotification;

class AppointmentService
{


        public function scheduleAppointment(Request $request)
        {
            $validated = $request->validate([
                'animal_case_id'  => 'required|exists:animal_cases,id',
                'scheduled_date'  => 'required|date|after_or_equal:today',
                'scheduled_time'  => 'required|date_format:H:i',
                'description'     => 'required|string|min:10'
            ]);

            $animalCase = AnimalCase::find($validated['animal_case_id']);
            $doctorId = auth()->id();
            $scheduledAt = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['scheduled_date'] . ' ' . $validated['scheduled_time']
            );

            $existsSameTime = Appointment::where('scheduled_at', $scheduledAt)
                ->where('status', 'scheduled')
                ->exists();

                $appointment = Appointment::create([
                'user_id'        => $animalCase->user_id,
                'employee_id'    => $doctorId,
                'animal_case_id' => $animalCase->id,
                'scheduled_at'   => $scheduledAt,
                'description'    => $validated['description'],
                'status'         => 'scheduled',
                'is_immediate'   => false
            ]);

            $animalCase->user->notify(new AppointmentStatusNotification($appointment, 'scheduled'));

            return response()->json([
                'status'       => true,
                'message'      => 'تم جدولة الموعد بنجاح' . ($existsSameTime ? ' ⚠ يوجد موعد آخر بنفس التوقيت' : ''),
                'appointment'  => $appointment->load('animalCase:id,name_animal,case_type,image'),
                'doctor'       => auth()->user()->only(['id', 'name']),
                'warning'      => $existsSameTime,
            ]);
        }

        public function updateAppointment(Request $request, $id)
        {
            $validated = $request->validate([
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'required|date_format:H:i',
                'description'    => 'nullable|string|min:10'
            ]);

            $appointment = Appointment::findOrFail($id);

            $scheduledAt = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['scheduled_date'] . ' ' . $validated['scheduled_time']
            );

          
            $appointment->update([
                'scheduled_at' => $scheduledAt,
                'description'  => $validated['description'] ?? $appointment->description,
            ]);

            $appointment->user->notify(new AppointmentStatusNotification($appointment));

            return response()->json([
                'status'      => true,
                'message'     => 'تم تعديل الموعد بنجاح',
                'appointment' => $appointment->load('animalCase:id,name_animal,case_type,image'),
            ]);
        }


}