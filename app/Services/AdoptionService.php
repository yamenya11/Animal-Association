<?php
namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\Request;
use  App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AdobtStatusAccept;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class AdoptionService
 {
     public function createAdoption(Request $request, $userId): array
    {

        $request->validate([
            'animal_id' => 'required|exists:animals,id',
        ]);
        $animal =Animal::find($request->animal_id);
        if ($animal->is_adopted ==1) {
            return [
                'status' => false,
                'message' => 'هذا الحيوان تم تبنيه بالفعل',
            ];
        }
         $adoption = Adoption::create([
            'user_id' => $userId,
            'animal_id' => $request->animal_id,
            'status' => 'pending',
        ]);

         return [
            'status' => true,
            'message' => '  م إنشاء طلب التبني بنجاح بانتظار الموافقة من قبل المسئول',
            'data' => $adoption,
        ];
    }

      public function getUserAdoptions()
    {
        $userId = Auth::id();
        return Adoption::join('animals','adoptions.animal_id','=','animals.id')
        ->select('animals.name','animals.type','animals.age','animals.health_info','animals.image')
            ->where('user_id', $userId)
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

        // إرسال الإشعار عبر الخدمة
        $notificationService = app(NotificationService::class);
        $notificationService->sendAdoptionStatusNotification($adoptionRequest);

        DB::commit();

        return [
            'status' => true,
            'message' => $status === 'approved' 
                ? 'تمت الموافقة على طلب التبني.' 
                : 'تم رفض طلب التبني.',
            'data' => $adoptionRequest,
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