<?php
namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\Request;
use  App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AdobtStatusAccept;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AnimalType;
use App\Services\NotificationService;
use App\Models\TemporaryCareRequest;
class AdoptionService
 {
public function createAdoption(Request $request, $userId): array
{
    $validated = $request->validate([
        'animal_name' => 'required|string|max:50',
        'animal_type' => 'required|string|max:50', // تغيير من animal_type_id إلى animal_type
        'address' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:10',
        'reason' => 'nullable|string|max:50',
    ]);

    // البحث عن النوع أولاً
    $animalType = \App\Models\AnimalType::where('name', $validated['animal_type'])->first();

    if (!$animalType) {
        return [
            'status' => false,
            'message' => 'نوع الحيوان غير مسجل',
        ];
    }

    $animal = Animal::where('name', $validated['animal_name'])
                  ->where('type_id', $animalType->id)
                  ->where('is_adopted', false)
                  ->first();

    if (!$animal) {
        return [
            'status' => false,
            'message' => 'الحيوان غير موجود أو تم تبنيه بالفعل',
        ];
    }

    $adoption = Adoption::create([
        'user_id' => $userId,
        'animal_id' => $animal->id,
        'type_id' => $animalType->id,
        'address' => $validated['address'] ?? 'غير محدد',
        'birth_date' => $animal->birth_date,
        'phone' => $validated['phone'],
        'reason' => $validated['reason'] ?? null,
        'status' => 'pending',
    ]);

    return [
        'status' => true,
        'message' => 'تم إنشاء طلب التبني بنجاح',
        'data' => $this->formatAdoptionRequest($adoption),
    ];
}

        protected function formatAdoptionRequest(Adoption $adoption): array
        {
            return [
                'request_id' => $adoption->id,
                'animal' => [
                    'id' => $adoption->animal->id,
                    'name' => $adoption->animal->name,
                    'type' => $adoption->animalType->name, 
                    'image_url' => $adoption->animal->image ? asset('storage/'.$adoption->animal->image) : null
                ],
                'user' => [
                    'id' => $adoption->user->id,
                    'name' => $adoption->user->name
                ],
                'status' => [
                    'code' => $adoption->status,
                    'display' => $this->getStatusDisplay($adoption->status)
                ],
                'created_at' => $adoption->created_at->format('Y-m-d H:i')
            ];
        }

// يمكن إعادة استخدام هذه الدالة من الخدمة الأخرى
        protected function getStatusDisplay(string $status): string
        {
            return match($status) {
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                default => $status
            };
        }

   public function getUserAdoptions()
{
    $userId = Auth::id();
    
    return Adoption::with(['animal.type', 'animal.images'])
        ->where('user_id', $userId)
        ->where('status', 'approved')
        ->get()
        ->map(function ($adoption) {
            return [
                'id' => $adoption->id,
                'animal' => [
                    'name' => $adoption->animal->name,
                    'type' => $adoption->animal->type->name,
                    'image' => $adoption->animal->image_url
                ],
                'status' => $adoption->status,
                'created_at' => $adoption->created_at->format('Y-m-d')
            ];
        });
}

 public function getAllAdoptions()
{
    return Adoption::with(['user', 'animal.type'])
        ->where('status', 'pending')
        ->get()
        ->map(function ($adoption) {
            return [
                'id' => $adoption->id,
                'user' => [
                    'name' => $adoption->user->name,
                    'email' => $adoption->user->email
                ],
                'animal' => [
                    'name' => $adoption->animal->name,
                    'type' => $adoption->animal->type->name
                ],
                'status' => $adoption->status,
                'created_at' => $adoption->created_at->format('Y-m-d H:i')
            ];
        });
}
public function accept_Adopt_Admin($adoptionId, $status): array
{
    $adoptionRequest = Adoption::with(['user', 'animal'])->findOrFail($adoptionId);

    if (!in_array($status, ['approved', 'rejected'])) {
        return [
            'status' => false,
            'message' => 'إجراء غير صالح.',
            'code' => 400
        ];
    }

    DB::beginTransaction();
    try {
        $adoptionRequest->status = $status;
        $adoptionRequest->save();

        if ($status === 'approved') {
            $animal = $adoptionRequest->animal;
            $animal->is_adopted = true;
            $animal->adopted_at = now();
            $animal->save();

            // إلغاء أي طلبات أخرى لنفس الحيوان
            Adoption::where('animal_id', $animal->id)
                ->where('id', '!=', $adoptionId)
                ->update(['status' => 'rejected']);
        }

        $adoptionRequest->load(['user', 'animal.type']);

       // $notificationService = app(NotificationService::class);
       // $notificationService->sendAdoptionStatusNotification($adoptionRequest);

        DB::commit();

        return [
            'status' => true,
            'message' => $status === 'approved' 
                ? 'تمت الموافقة على طلب التبني.' 
                : 'تم رفض طلب التبني.',
            'data' => $this->formatAdoptionRequest($adoptionRequest),
            'code' => 200
        ];

    } catch (\Exception $e) {
        DB::rollBack();
        return [
            'status' => false,
            'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage(),
            'code' => 500
        ];
    }
}

}