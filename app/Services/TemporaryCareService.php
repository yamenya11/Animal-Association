<?php

namespace App\Services;

use App\Models\TemporaryCareRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Models\Animal;
class TemporaryCareService
{
   
 // routes/api.php

// TemporaryCareController.php

   public function createRequest(Request $request)
{
    $validated = $request->validate([
        'animal_name' => 'required|string|max:255',
        'type' => 'required|string|max:50',
        'address' => 'required|string|max:255',
        'duration' => 'required|in:week,month,custom',
        'custom_duration' => 'required_if:duration,custom|string|max:100',
        'health_info' => 'required|string',
        'breed' => 'nullable|string|max:100',
        'vet_id' => 'nullable|exists:users,id'
    ]);

    return DB::transaction(function () use ($validated) {
        $animal = Animal::where('name', $validated['animal_name'])
                      ->where('purpose', 'temporary_care')
                      ->where('available_for_care', true)
                      ->where('is_adopted', false)
                      ->firstOrFail();

        $request = TemporaryCareRequest::create([
            'user_id' => Auth::id(),
            'animal_id' => $animal->id,
            'type' => $validated['type'],
            'address' => $validated['address'],
            'breed' => $validated['breed'] ?? null,
            'duration' => $validated['duration'],
            'custom_duration' => $validated['custom_duration'] ?? null,
            'health_info' => $validated['health_info'],
            'vet_id' => $validated['vet_id'] ?? null,
            'status' => 'pending'
        ]);

        $animal->update(['available_for_care' => false]);

        return [
            'status' => true,
            'message' => 'تم تقديم طلب الرعاية المؤقتة بنجاح',
            'data' => $this->formatRequest($request),
            'code' => 201
        ];
    });
}

protected function formatRequest(TemporaryCareRequest $request): array
{
    return [
        'request_id' => $request->id,
        'animal' => [
            'id' => $request->animal->id,
            'name' => $request->animal->name,
            'type' => $request->animal->type,
            'breed' => $request->animal->breed,
            'image_url' => $request->animal->image ? asset('storage/'.$request->animal->image) : null
        ],
        'care_details' => [
            'address' => $request->address,
            'duration' => $request->custom_duration 
                ? $request->duration . ' (' . $request->custom_duration . ')'
                : $request->duration,
            'health_notes' => $request->health_info,
            'vet_id' => $request->vet_id
        ],
        'status' => [
            'code' => $request->status,
            'display' => $this->getStatusDisplay($request->status)
        ],
        'created_at' => $request->created_at->format('Y-m-d H:i')
    ];
}

protected function formatAdminRequest(TemporaryCareRequest $request): array
{
    return [
        'id' => $request->id,
        'user' => [
            'name' => $request->user->name,
            'phone' => $request->user->phone
        ],
        'animal' => [
            'name' => $request->animal->name,
            'type' => $request->animal->type,
            'breed' => $request->animal->breed,
            'image_url' => $request->animal->image ? asset('storage/'.$request->animal->image) : null
        ],
        'request_date' => $request->created_at->format('Y-m-d H:i'),
        'status' => $this->getStatusDisplay($request->status),
        'vet_id' => $request->vet_id
    ];
}

protected function getStatusDisplay(string $status): string
{
    return match($status) {
        'pending' => 'قيد الانتظار',
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
        default => $status
    };
}

    public function getUserRequests()
    {
        $requests = TemporaryCareRequest::with(['user:id,name', 'animal'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return $requests->map(function($request) {
            return $this->formatRequest($request);
        });
    }

    public function getProcessedRequests()
{
    $requests = TemporaryCareRequest::with(['user:id,name', 'animal'])
        ->where('user_id', Auth::id())
        ->whereIn('status', ['approved', 'rejected'])
        ->latest()
        ->get()
        ->groupBy('status');

    return [
        'approved' => $requests->get('approved', collect())->map(function($request) {
            return $this->formatRequest($request);
        })->values(),
        'rejected' => $requests->get('rejected', collect())->map(function($request) {
            return $this->formatRequest($request);
        })->values()
    ];
}

public function getAvailableAnimals()
{
    try {
        $animals = Animal::with('type') // تحميل العلاقة مع animal_types
                       ->where('purpose', 'temporary_care')
                       ->where('available_for_care', true)
                       ->where('is_adopted', false)
                       ->select('id', 'name', 'type_id', 'birth_date', 'health_info', 'image')
                       ->get()
                       ->map(function($animal) {
                           return $this->formatAnimal($animal);
                       });

        return [
            'status' => true,
            'data' => $animals,
            'count' => $animals->count()
        ];

    } catch (\Exception $e) {
        return [
            'status' => false,
            'message' => 'حدث خطأ أثناء جلب الحيوانات المتاحة',
            'error' => $e->getMessage()
        ];
    }
}

protected function formatAnimal(Animal $animal)
{
    return [
        'id' => $animal->id,
        'name' => $animal->name,
        'type' => $animal->type->name, // الوصول عبر العلاقة
        'age' => $this->calculateAge($animal->birth_date),
        'health_status' => $animal->health_info,
        'image_url' => $animal->image ? asset('storage/'.$animal->image) : null
    ];
}

protected function calculateAge($birthDate)
{
    if (!$birthDate) return 'غير معروف';
    
    $age = now()->diff($birthDate);
    $years = $age->y;
    $months = $age->m;
    
    $result = [];
    if ($years > 0) $result[] = $years . ' سنة';
    if ($months > 0) $result[] = $months . ' أشهر';
    
    return $result ? implode(' و', $result) : 'أقل من شهر';
}

    public function getAllRequests()
    {
         return TemporaryCareRequest::with(['user', 'animal.type'])
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

    /**
     * تغيير حالة الطلب من قبل المسؤول (قبول أو رفض)
     */
    public function respondToRequest(int $requestId, string $status): array
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            return [
                'status' => false,
                'message' => 'حالة غير صالحة',
                'code' => 400,
            ];
        }

        try {
            DB::beginTransaction();

            $request = TemporaryCareRequest::with(['user', 'animal'])->findOrFail($requestId);
            $request->status = $status;
            $request->save();

            // إشعار المستخدم
            //$notificationService = app(NotificationService::class);
            //$notificationService->sendTemporaryCareStatusNotification($request);

            DB::commit();

            return [
                'status' => true,
                'message' => $status === 'approved' 
                    ? 'تمت الموافقة على طلب الرعاية المؤقتة.'
                    : 'تم رفض طلب الرعاية المؤقتة.',
                'data' => $request->fresh(),
                'code' => 200
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء المعالجة: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
