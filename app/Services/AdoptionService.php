<?php
namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\Request;
use  App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AdobtStatusAccept;
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

    public function accept_Adopt_Admin($adoptionId,$status): array{
 
        $User_adopt = Adoption::findOrFail($adoptionId);

        if (!in_array($status, ['approved', 'rejected'])) {
        return [
            'status' => false,
            'message' => 'إجراء غير صالح.',
        ];
    }

     $User_adopt->status ='approved'; 
       $User_adopt->status = $status;

     $User_adopt->save();

if ($status === 'approved') {
        $animal = $User_adopt->animal;
        $animal->is_adopted = true;
        $animal->save();
    }
$User_adopt->user->notify(new AdobtStatusAccept($User_adopt));
       return [
        'status' => true,
        'message' => $status === 'approved'
            ? 'تمت الموافقة على طلب التبني.'
            : 'تم رفض طلب التبني.',
        
            'data' => $User_adopt,
    ];



    }

}