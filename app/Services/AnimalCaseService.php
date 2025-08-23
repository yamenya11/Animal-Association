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
   


  
      $responseData = $case->toArray();
    if ($case->image) {
        $responseData['image_url'] = config('app.url') . '/storage/' . $case->image;
    }


    return [
        'status' => true,
        'message' => 'تم إنشاء الحالة بنجاح',
        'data' => $responseData,
        
    ];
}

protected function handleEmergencyCase(AnimalCase $case)
{
    $scheduledTime = now()->addMinutes(5);
    
    $employee = User::role('employee')
                  ->where('available', true)
                  ->first();

    // الحصول على سيارة إسعاف متاحة
    $ambulance = Ambulance::where('status', 'available')->first();

    $appointment = Appointment::create([
        'user_id' => $case->user_id,
        'employee_id' => $employee ? $employee->id : null,
        'animal_case_id' => $case->id,
        'scheduled_date' => $scheduledTime->format('Y-m-d'),
        'scheduled_time' => $scheduledTime->format('H:i:s'),
        'status' => 'scheduled',
        'is_immediate' => true,
        'ambulance_id' => $ambulance ? $ambulance->id : null,
        'description' => 'طلب طارئ - ' . ($ambulance ? 
                        "سيارة إسعاف: {$ambulance->plate_number}" : 
                        'بانتظار سيارة إسعاف')
    ]);

    if ($ambulance) {
        $ambulance->update(['status' => 'on_mission']);
        $this->dispatchAmbulance(
            $case->emergency_address,
            $case->emergency_phone,
            $ambulance
        );
    }

    return $appointment;
}

protected function dispatchAmbulance(string $address, string $phone, Ambulance $ambulance)
{
    // يمكنك هنا إرسال إشعار للسائق أو الاتصال بـ API خارجي
    
    return [
        'success' => true,
        'message' => 'تم إرسال فريق طبي إلى الموقع',
        'ambulance' => [
            'plate_number' => $ambulance->plate_number,
            'driver_name' => $ambulance->driver_name,
            'driver_phone' => $ambulance->driver_phone
        ],
        'estimated_arrival' => now()->addMinutes(5)->format('H:i')
    ];
}

public function getApprovedCases()
{
    return AnimalCase::where('approval_status', 'approved')
        ->with(['user', 'animal'])
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