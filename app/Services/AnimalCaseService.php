<?php
namespace App\Services;
use App\Models\User; // أضف هذا الاستيراد
use App\Models\AnimalCase;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\sendImmediateCaseNotification;
use App\Services\NotificationService;
class AnimalCaseService
{
      // إنشاء حالة جديدة مع ربط المستخدم تلقائياً
    public function createCase(Request $request): array
    {
        $validated = $request->validate([
            'name_animal' => 'required|string|max:255',
            'case_type' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'request_type' => 'required|in:regular,immediate',
        ]);

        // معالجة الصورة إذا وجدت
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // إنشاء اسم فريد للملف مع الحفاظ على الامتداد
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            // تخزين الملف في المسار 'images/photocaseanimal' داخل مجلد التخزين العام
            $path = $file->storeAs('images/photocaseanimal', $fileName, 'public');
            // حفظ المسار في البيانات المعتمدة (للتخزين في قاعدة البيانات)
            $validated['image'] = $path;
        }

        $validated['user_id'] = Auth::id();
        $case = AnimalCase::create($validated);

        // إذا كان الطلب فوري
        if ($validated['request_type'] === 'immediate') {
            // الحصول على الموظف المسؤول (مثال: أول موظف متاح)
            $employee = User::role('employee')
                ->orderBy('available', 'desc')
                ->first();

            if ($employee) {
                $appointment = Appointment::create([
                    'user_id' => $validated['user_id'],
                    'animal_case_id' => $case->id,
                    'employee_id' => $employee->id, // تعيين الموظف
                    'scheduled_at' => now()->addMinutes(1),
                    'status' => 'pending',
                    'is_immediate' => true,
                ]);
                $notificationService = app(NotificationService::class);
                // إرسال إشعار للموظف
                $notificationService->sendImmediateCaseNotification($employee, $case, $appointment);
            }
        }

        // إضافة رابط الصورة ليتم عرضه بشكل صحيح في الواجهة (إن وجدت)
        if (isset($case->image)) {
            $case->image_url = asset('storage/' . $case->image);
        }

        return [
            'status' => true,
            'message' => 'تم إنشاء الحالة بنجاح',
            'data' => $case,
        ];
    }



  
    // جلب الحالات المرتبطة بالمستخدم الحالي فقط
    public function getAnimalCasesByUser()
    {
        return AnimalCase::where('user_id', Auth::id())
                         ->orderBy('created_at', 'desc')
                         ->get();
    }

}