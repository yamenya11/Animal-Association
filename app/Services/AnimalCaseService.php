<?php

namespace App\Services;

use App\Models\User;
use App\Models\AnimalCase;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Ambulance; 

class AnimalCaseService
{
    
public function createCase(Request $request): array
{
    $validated = $request->validate([
        'name_animal' => 'required|string|max:255',
        'case_type' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image|max:2048',
        'request_type' => 'required|in:regular,immediate',
        'emergency_address' => 'required_if:request_type,immediate|string|max:255',
        'emergency_phone' => 'required_if:request_type,immediate|string|max:20'
    ]);

    // معالجة الصورة
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images/photocaseanimal', 'public');
    }

    // تحضير بيانات الحالة
    $caseData = [
        'user_id' => Auth::id(),
        'name_animal' => $validated['name_animal'],
        'case_type' => $validated['case_type'],
        'description' => $validated['description'],
        'image' => $imagePath,
        'request_type' => $validated['request_type'],
    ];

    // إضافة حقول الطوارئ إذا كانت الحالة طارئة
    if ($validated['request_type'] === 'immediate') {
        $caseData['emergency_address'] = $validated['emergency_address'];
        $caseData['emergency_phone'] = $validated['emergency_phone'];
        $caseData['approval_status'] = 'approved';
    } else {
        $caseData['approval_status'] = 'pending';
    }

    // إنشاء الحالة
    $case = AnimalCase::create($caseData);

   $responseData = $case->toArray();
    
    if ($case->image) {
        $responseData['image_url'] = config('app.url') . '/storage/' . $case->image;
    }

    // معالجة الحالات الطارئة
    if ($validated['request_type'] === 'immediate') {
        $result = $this->handleEmergencyCase($case);
        
        // إضافة بيانات الموعد
        $responseData['appointment'] = [
            'id' => $result['appointment']->id,
             'scheduled_at'=> $result['appointment']->scheduled_at
                ? $result['appointment']->scheduled_at->toIso8601String()
                : null,

            'status' => $result['appointment']->status,
            'description' => $result['appointment']->description
        ];
        
        if ($result['employee']) {
            $result['employee']->notify(
                new \App\Notifications\ImmediateCaseNotification($case, $result['appointment'])
            );
        }
        if ($result['ambulance_available']) {
            // **إضافة معلومات السيارة**
            $responseData['ambulance_info'] = $result['ambulance_info'];
            $responseData['message'] = '🚑 سيارة الإسعاف في طريقها إليك';
        } else {
            // **إضافة معلومات الفرع**
            $responseData['branch_info'] = [
                'address' => 'دمشق - المزة',
                'phone' => '011 123 4567',
                'whatsapp' => '011 123 4567',
                'work_hours' => '8:00 صباحاً - 6:00 مساءً'
            ];
            $responseData['message'] = '📞 للاستفسار اتصل على: 011 123 4567';
        }
    }

    return [
        'status' => true,
        'message' => 'تم إنشاء الحالة بنجاح',
        'data' => $responseData
    ];
}
protected function handleEmergencyCase(AnimalCase $case): array
{
    $scheduledTime = now()->addMinutes(5);
    
    $employee = User::role('employee')
                  ->where('available', true)
                  ->first();

    $ambulance = Ambulance::where('status', 'available')->first();

    $description = $ambulance
        ? 'طلب طارئ - سيارة إسعاف متاحة'
        : 'طلب طارئ - يرجى التوجه إلى أقرب فرع';

        $appointment = Appointment::create([
        'user_id'       => $case->user_id,
        'employee_id'   => $employee?->id,
        'animal_case_id'=> $case->id,
        'scheduled_at'  => now()->addMinutes(5), // datetime كامل
        'status'        => 'completed',
        'is_immediate'  => true,
        'description'   => $description,
    ]);


    if ($ambulance) {
        $ambulance->update(['status' => 'on_mission']);
        return [
            'success' => true,
            'employee' => $employee,
            'ambulance_available' => true,
            'appointment' => $appointment,
            'ambulance_info' => [
                'driver_name' => $ambulance->driver_name,
                'driver_phone' => $ambulance->driver_phone,
                'estimated_arrival' => '5 دقائق'
            ]
        ];
    } else {
        return [
            'success' => true,
            'employee' => $employee,
            'ambulance_available' => false,
            'appointment' => $appointment
        ];
    }
}


public function getApprovedCases()
{
    return AnimalCase::where('approval_status', 'approved')
        ->where('request_type','regular')
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function($case) {
            $caseData = $case->toArray();
            if ($case->image) {
                $caseData['image_url'] = config('app.url') . '/storage/' . $case->image;
            }
            return $caseData;
        });
}
        public function getAnimalCasesByUser()
        {
            $userId = Auth::id();
            return AnimalCase::with('user')
                ->where('approval_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($case) {
                    return [
                        'id' => $case->id,
                        'animal_name' => $case->name_animal,
                        'case_type' => $case->case_type,
                        'image_url' => $case->image ? config('app.url') . '/storage/' . $case->image : null,
                        'created_at' => $case->created_at->format('Y-m-d H:i'),
                        'approval_status' => $case->approval_status
                    ];
                });
        }


}