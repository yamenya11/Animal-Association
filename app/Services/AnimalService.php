<?php
namespace App\Services;
use App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\AnimalType;
use Illuminate\Http\JsonResponse;

class AnimalService
{
public function getAvailableAnimals()
{
    return Animal::select(
        'animals.id',
        'animals.name',
        'animal_types.name as type', 
        'animals.breed',
        'animals.birth_date',
        'animals.health_info',
        'animals.image',
        'animals.is_adopted'
    )
    ->join('animal_types', 'animals.type_id', '=', 'animal_types.id')
    ->where('is_adopted', false)
    ->where('purpose', 'adoption')
    ->get();
}
public function create(Request $request,$userId): array
{
 
 $userId = Auth::id(); // <-- احصل على المستخدم الحالي

    if (!$userId) {
        return [
            'status' => false,
            'message' => 'يجب تسجيل الدخول أولاً.',
        ];
    }

    // التحقق من البيانات القادمة من الطلب
    $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'type' => 'required|string|max:50',
        'purpose' => 'required|in:adoption,temporary_care',
        'breed'       => 'nullable|string|max:100',
        'birth_date'  => 'nullable|date',
        'health_info' => 'nullable|string',
        'image'       => 'nullable|image|max:2048',
        'describtion'     => 'nullable|string|max:255',
        // 'phone'       => 'nullable|string|max:20',
    ]);
     $animalType = AnimalType::firstOrCreate([
        'name' => $validated['type']
    ]);
$userId = Auth::id();
    // تحميل الصورة إن وجدت
    if ($request->hasFile('image')) {
        $filename = uniqid() . '.' . $request->image->getClientOriginalExtension();
        $path = $request->image->storeAs('animal_images', $filename, 'public');
        $validated['image'] = $path;
    }
  $validated['user_id'] = $userId; 
    // إنشاء الحيوان بالطريقة التي طلبتها
    $animal = Animal::create([
        'user_id'     => $userId,
        'name'        => $validated['name'],
        'type_id' => $animalType->id,
        'purpose' => $validated['purpose'],
        'available_for_care' => $validated['purpose'] == 'temporary_care',
        'breed'       => $validated['breed'] ?? null,
        'birth_date'  => $validated['birth_date'] ?? null,
        'health_info' => $validated['health_info'] ?? null,
        'image'       => $validated['image'] ?? null,
        'describtion'       => $validated['describtion'] ?? null,
    ]);

    return [
        'status'  => true,
        'message' => 'تمت إضافة الحيوان بنجاح.',
        'data'    => $animal,
    ];
}

// app/Services/AnimalService.php






    public function update(Request $request, Animal $animal): array
    {
        try {
            // التحقق من البيانات
            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|max:50',
                'breed' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'health_info' => 'nullable|string',
                'describtion' => 'nullable|string|max:255',
                'purpose' => 'sometimes|required|in:adoption,temporary_care',
                'image' => 'nullable|image|max:2048',
            ]);

            // إذا تم إرسال نوع جديد
            if ($request->has('type')) {
                $animalType = AnimalType::firstOrCreate(['name' => $request->type]);
                $data['type_id'] = $animalType->id;
                unset($data['type']);
            }

            // تحديث حقل available_for_care تلقائيًا حسب purpose
            if (isset($data['purpose'])) {
                $data['available_for_care'] = $data['purpose'] === 'temporary_care';
            }

            // تحديث الصورة إن وجدت
            if ($request->hasFile('image')) {
                if ($animal->image) {
                    Storage::disk('public')->delete($animal->image);
                }
                $data['image'] = $request->image->store('animal_images', 'public');
            }

            if (empty($data)) {
                return [
                    'status' => false,
                    'message' => 'لا توجد بيانات لتحديثها',
                    'data' => null
                ];
            }

            $updated = $animal->update($data);

            if (!$updated) {
                return [
                    'status' => false,
                    'message' => 'فشل في تحديث بيانات الحيوان',
                    'data' => null
                ];
            }

            $animal->refresh()->load('type');

            return [
                'status' => true,
                'message' => 'تم تحديث بيانات الحيوان بنجاح',
                'data' => $animal
            ];

        } catch (\Exception $e) {
            Log::error('Animal update error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }



  public function delete(int $animalId): bool
{
    $animal = Animal::find($animalId);
    
    if (!$animal) {
        return false;
    }

    // حذف الصورة إذا وُجدت
    if ($animal->image) {
        Storage::disk('public')->delete($animal->image);
    }

    return $animal->delete();
}

}
   