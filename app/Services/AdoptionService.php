<?php
namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\Request;
use  App\Models\Animal;
use Illuminate\Support\Facades\Auth;
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
            'message' => 'تم إنشاء طلب التبني بنجاح',
            'data' => $adoption,
        ];

    }

      public function getUserAdoptions($userId)
    {
        return Adoption::with('animal')
            ->where('user_id', $userId)
            ->get();
    }

}