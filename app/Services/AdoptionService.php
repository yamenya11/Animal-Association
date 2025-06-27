<?php
namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\Request;
use  App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AdobtStatusAccept;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\NotificationService;

class AdoptionService
 {
  public function createAdoption(Request $request, $userId): array
{
    $request->validate([
        'animal_name' => 'required|string|max:50',  // اسم الحيوان للبحث
       // 'name'        => 'required|string|max:50',  // اسم المتبني
        'type'        => 'nullable|string|max:50',
        'address'     => 'nullable|string|max:255',
        'birth_date'  => 'nullable|date',
        'phone'       => 'nullable|string|max:10',
    ]);

    // البحث عن الحيوان حسب اسم الحيوان وليس اسم المتبني
    $animal = Animal::where('name', $request->animal_name)->first();

    if (!$animal) {
        return [
            'status' => false,
            'message' => 'الحيوان غير موجود في النظام.',
        ];
    }

    if ($animal->is_adopted == 1) {
        return [
            'status' => false,
            'message' => 'هذا الحيوان تم تبنيه بالفعل',
        ];
    }

    $adoption = Adoption::create([
        'user_id'    => $userId,
        'animal_id'  => $animal->id,
    //    'name'       => $request->name,    // اسم المتبني من الطلب
        'type'       => $animal->type,
        'address'    => $request->address ?? 'not set',
        'birth_date' => $animal->birth_date,
        'phone'      => $request->phone,
        'status'     => 'pending',
    ]);

    return [
        'status' => true,
        'message' => 'تم إنشاء طلب التبني بنجاح بانتظار الموافقة من قبل المسئول',
        'data' => $adoption,
    ];
}

      public function getUserAdoptions()
    {
        $userId = Auth::id();
        return Adoption::join('animals','adoptions.animal_id','=','animals.id')
        ->select('animals.name','animals.type','animals.birth_date','animals.health_info','animals.image')
            ->where('adoptions.user_id', $userId)
            ->where('adoptions.status', 'approved')
            ->get();
    }

    public function getAllAdoptions()
      { 
    return Adoption::select([
            'adoptions.id',
            'adoptions.status',
            'users.name as name_usre',
            'users.email as email_user',
            'animals.name as animal_name',
            'animals.type as animal_type',
        ])
        ->leftJoin('users', 'adoptions.user_id', '=', 'users.id')
        ->leftJoin('animals', 'adoptions.animal_id', '=', 'animals.id')
        ->where('adoptions.status', 'pending')
        ->get();
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
            $animal->save();
        }
      $adoptionRequest->load(['user', 'animal']);
        // إرسال الإشعار عبر الخدمة
        $notificationService = app(NotificationService::class);
        $notificationService->sendAdoptionStatusNotification($adoptionRequest);

        DB::commit();

        return [
            'status' => true,
            'message' => $status === 'approved' 
                ? 'تمت الموافقة على طلب التبني.' 
                : 'تم رفض طلب التبني.',
            'data' => $adoptionRequest->fresh(),
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