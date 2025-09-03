<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\AnimalService;
use Illuminate\Support\Facades\Auth;
use App\Models\Animal;
use App\Models\VolunteerType;
use App\Models\AnimalType;

class EmployeeController extends Controller
{
   protected $animalService;

    public function __construct(AnimalService $animalService)
    {
        $this->animalService = $animalService;
    }

        public function filte_index(Request $request): JsonResponse
        {
            $query = Animal::with(['type', 'user']);

            if ($request->has('purpose') && in_array($request->purpose, ['adoption', 'temporary_care'])) {
                $query->where('purpose', $request->purpose);
            }

            $animals = $query->get()->map(function($animal) {
                $animalData = $animal->toArray();
                if ($animal->image) {
                    $animalData['image_url'] = config('app.url') . '/storage/' . $animal->image;
                }
                return $animalData;
            });

            return response()->json([
                'status' => true,
                'data' => $animals
            ]);
        }

        public function adoptions(): JsonResponse
        {
            $animals = Animal::with(['type', 'user'])
                ->where('purpose', 'adoption')
                ->where('is_adopted', false)
                ->get()
                ->map(function($animal) {
                    $animalData = $animal->toArray();
                    if ($animal->image) {
                        $animalData['image_url'] = config('app.url') . '/storage/' . $animal->image;
                    }
                    return $animalData;
                });

            return response()->json([
                'status' => true,
                'data' => $animals
            ]);
        }

        public function temporaryCare(): JsonResponse
        {
            $animals = Animal::with(['type', 'user'])
                ->where('purpose', 'temporary_care')
                ->where('available_for_care', true)
                ->where('is_adopted', false)
                ->get()
                ->map(function($animal) {
                    $animalData = $animal->toArray();
                    if ($animal->image) {
                        $animalData['image_url'] = config('app.url') . '/storage/' . $animal->image;
                    }
                    return $animalData;
                });

            return response()->json([
                'status' => true,
                'data' => $animals
            ]);
        }

            public function index(): JsonResponse
        {
            $animals = Animal::with(['type', 'user'])
                ->get()
                ->map(function($animal) {
                    $animalData = $animal->toArray();
                    if ($animal->image) {
                        $animalData['image_url'] = config('app.url') . '/storage/' . $animal->image;
                    }
                    return $animalData;
                });
                
            return response()->json(['status' => true, 'data' => $animals]);
        }

            public function show($id): JsonResponse
        {
            $animal = Animal::with(['type', 'user'])->findOrFail($id);
            
            $animalData = $animal->toArray();
            if ($animal->image) {
                $animalData['image_url'] = config('app.url') . '/storage/' . $animal->image;
            }
            
            return response()->json(['status' => true, 'data' => $animalData]);
        }

     public function getAvailableAnimals(): JsonResponse
    {
        $animals = $this->animalService->getAvailableAnimals();
        return response()->json(['status' => true, 'data' => $animals]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $animal = $this->animalService->create($request,$userId);
        return response()->json(['status' => true, 'data' => $animal]);
    }
 public function update(Request $request, Animal $animal): JsonResponse
    {
        $result = $this->animalService->update($request, $animal);

        return response()->json($result, $result['status'] ? 200 : 400);
    }
public function destroy(int $animalId)
{
    $deleted = $this->animalService->delete($animalId);
    
    return response()->json([
        'status' => $deleted,
        'message' => $deleted 
            ? 'تم حذف الحيوان بنجاح' 
            : 'لم يتم العثور على الحيوان'
    ], $deleted ? 200 : 404);
}

    public function updateAvailability(Animal $animal)
{
    $animal->update([
        'available_for_care' => !$animal->available_for_care
    ]);

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث حالة التوفر',
        'is_available' => $animal->available_for_care
    ]);
}

  public function getAllTypes(): JsonResponse
    {
        $types = AnimalType::all();
        return response()->json(['status' => true, 'data' => $types]);
    }



public function uploadImage(Request $request): JsonResponse
{
    $request->validate([
        'image' => 'required|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $filename = uniqid() . '.' . $request->image->getClientOriginalExtension();
        $path = $request->image->storeAs('animal_images', $filename, 'public');
        
        return response()->json([
            'status' => true,
            'message' => 'تم رفع الصورة بنجاح',
            'path' => $path,
            'url' => config('app.url') . '/storage/' . $path
        ]);
    }

    return response()->json([
        'status' => false,
        'message' => 'فشل في رفع الصورة'
    ], 400);
}

public function updatePurpose(Request $request, $animalId)
{
    $request->validate([
        'purpose' => 'required|in:adoption,temporary_care,null'
    ]);

    $animal = Animal::findOrFail($animalId);

    $finalPurpose = $request->purpose === 'null' ? null : $request->purpose;

    $updates = [
        'purpose' => $finalPurpose,
        'available_for_care' => $finalPurpose === 'temporary_care'
    ];

    // إذا كان الغرض التبني، نضمن أن is_adopted = false
    if ($finalPurpose === 'adoption') {
        $updates['is_adopted'] = false;
    }

    $animal->update($updates);

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث الغرض بنجاح',
        'data' => $animal->fresh()
    ]);
}

    // AnimalStatusController.php
public function updatestatusavail(Request $request, Animal $animal)
{
    $request->validate([
        'is_adopted' => 'sometimes|boolean',
        'available_for_care' => 'sometimes|boolean'
    ]);

    DB::beginTransaction();
    try {
        $updates = [];
        
        if ($request->has('is_adopted')) {
            $updates['is_adopted'] = $request->is_adopted;
            $updates['available_for_care'] = false;
            $updates['adopted_at'] = $request->is_adopted ? now() : null;
        }

        if ($request->has('available_for_care')) {
            if ($animal->is_adopted) {
                throw new \Exception('لا يمكن تحديث حالة الرعاية لحيوان متبنى');
            }
            $updates['available_for_care'] = $request->available_for_care;
            $updates['is_adopted'] = false;
        }

        $animal->update($updates);
        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'تم التحديث بنجاح',
            'data' => $animal->fresh()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}




    // التقارير
    public function getDailyReport(): JsonResponse
    {
        $report = $this->employeeService->generateDailyReport();
        return response()->json([
            'status' => true,
            'data' => $report
        ]);
    }

   
    // إدارة الفعاليات
    public function getAvailableEvents(): JsonResponse
    {
        $events = Event::where('status', 'active')
            ->where('start_date', '>', now())
            ->with(['creator', 'participants'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }

    public function registerForEvent($eventId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($eventId);

            // التحقق من حالة الفعالية
            if ($event->status !== 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'الفعالية غير متاحة للتسجيل'
                ], 400);
            }

            // التحقق من عدد المشاركين
            if ($event->max_participants && $event->participants()->count() >= $event->max_participants) {
                return response()->json([
                    'status' => false,
                    'message' => 'عذراً، الفعالية مكتملة العدد'
                ], 400);
            }

            // التحقق من عدم التسجيل مسبقاً
            $existingRegistration = EventParticipant::where('event_id', $eventId)
                ->where('user_id', auth()->id())
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'status' => false,
                    'message' => 'أنت مسجل بالفعل في هذه الفعالية'
                ], 400);
            }

            // التسجيل في الفعالية
            $participant = EventParticipant::create([
                'event_id' => $eventId,
                'user_id' => auth()->id(),
                'status' => 'registered'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم التسجيل في الفعالية بنجاح',
                'data' => $participant
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التسجيل في الفعالية',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function cancelEventRegistration($eventId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $registration = EventParticipant::where('event_id', $eventId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $registration->update([
                'status' => 'cancelled'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم إلغاء التسجيل في الفعالية بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إلغاء التسجيل في الفعالية',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getMyEvents(): JsonResponse
    {
        $events = EventParticipant::with(['event.creator'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }
} 