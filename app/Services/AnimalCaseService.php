<?php

namespace App\Services;

use App\Models\User;
use App\Models\AnimalCase;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        'request_type' => $validated['request_type']
    ];

    // إضافة حقول الطوارئ إذا كانت الحالة طارئة
    if ($validated['request_type'] === 'immediate') {
        $caseData['emergency_address'] = $validated['emergency_address'];
        $caseData['emergency_phone'] = $validated['emergency_phone'];
    }

    // إنشاء الحالة
    $case = AnimalCase::create($caseData);

    // معالجة الحالات الطارئة
    if ($validated['request_type'] === 'immediate') {
        $this->handleEmergencyCase($case);
    }

    // إضافة رابط الصورة للاستجابة
    $responseData = $case->toArray();
    if ($case->image) {
        $responseData['image_url'] = asset('storage/' . $case->image);
    }

    return [
        'status' => true,
        'message' => 'تم إنشاء الحالة بنجاح',
        'data' => $responseData,
    ];
}

    protected function handleEmergencyCase(AnimalCase $case)
    {
        // 1. إرسال سيارة الإسعاف (تنفيذ وهمي)
        $ambulanceResponse = $this->dispatchAmbulance(
            $case->emergency_address,
            $case->emergency_phone
        );

        // 2. تعيين موظف متاح
        $employee = User::role('employee')
                      ->where('available', true)
                      ->orderBy('id')
                      ->first();

        if ($employee) {
            $appointment = Appointment::create([
                'user_id' => $case->user_id,
                'animal_case_id' => $case->id,
                'employee_id' => $employee->id,
                'scheduled_at' => now()->addMinutes(5), 
                'status' => 'pending',
                'is_immediate' => true,
                'emergency_data' => json_encode([
                'address' => $case->emergency_address,
                'phone' => $case->emergency_phone,
                'ambulance_dispatched' => $ambulanceResponse['success']
                ])
            ]);

            // يمكنك هنا إضافة أي إشعارات أو خدمات أخرى
        }
    }

   protected function dispatchAmbulance(string $address, string $phone): array
{
    // إضافة تنفيذ حقيقي هنا لو كان لديك API للإسعاف
    return [
        'success' => true,
        'message' => 'تم إرسال فريق طبي إلى الموقع',
        'dispatch_time' => now()->format('Y-m-d H:i:s'),
        'estimated_arrival' => now()->addMinutes(5)->format('H:i')
    ];
}

    public function getAnimalCasesByUser()
    {
        return AnimalCase::where('user_id', Auth::id())
                       ->orderBy('created_at', 'desc')
                       ->get()
                       ->map(function($case) {
                           if ($case->image) {
                               $case->image_url = asset('storage/' . $case->image);
                           }
                           return $case;
                       });
    }
}