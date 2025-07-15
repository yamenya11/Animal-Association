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
        'request_type' => $validated['request_type'],
        
    ];

    // إضافة حقول الطوارئ إذا كانت الحالة طارئة
    if ($validated['request_type'] === 'immediate') {
        $caseData['emergency_address'] = $validated['emergency_address'];
        $caseData['emergency_phone'] = $validated['emergency_phone'];
    }else {
        // إضافة approval_status فقط للطلبات العادية
        $caseData['approval_status'] = 'pending';
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
    // 1. حساب وقت بعد 5 دقائق
    $scheduledTime = now()->addMinutes(5);
    
    // 2. البحث عن موظف متاح (بنفس الطريقة الحالية)
    $employee = User::role('employee')
                  ->where('available', true)
                  ->orderBy('id')
                  ->first();

    // 3. إنشاء الموعد (باستخدام الحقلين المنفصلين)
    Appointment::create([
        'user_id' => $case->user_id,
        'employee_id' => $employee ? $employee->id : null,
        'animal_case_id' => $case->id,
        'scheduled_date' => $scheduledTime->format('Y-m-d'), // تاريخ منفصل
        'scheduled_time' => $scheduledTime->format('H:i:s'), // وقت منفصل
        'status' => $employee ? 'scheduled' : 'pending',
        'is_immediate' => true,
        'description' => 'طلب طارئ - بانتظار التأكيد'
    ]);

    // 4. إرسال الإسعاف (الكود الحالي)
    $this->dispatchAmbulance(
        $case->emergency_address,
        $case->emergency_phone
    );
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
                'image_url' => $case->image ? asset('storage/' . $case->image) : null,
                'created_at' => $case->created_at->format('Y-m-d H:i'),
                'approval_status'=>$case->approval_status
            ];
        });
}
}