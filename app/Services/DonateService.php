<?php

namespace App\Services;
use App\Models\Donate;
use Illuminate\Support\Facades\Auth;

class DonateService{


    public function store(Request $req){
   
        $req->validate([
          'full name'=>'required|string|max:255',
          'number'=>'required|string|max:15',
          'donation_type'=>'required|in:animal_supplies,financial',
          'ammount'=>'required|numeric',
          'notes'=>'nullable|string',
        ]);
        Donate::create($req->all());
        return ['status' => true, 'message' => 'تم  طلب التبرع انتظر ليتم الموافقة عليه.', 'data' => $req];
    }


     public function respondToPost($donateId, bool $action)
    {
            $donate = Donate::findOrFail($donateId);
    $donate->is_approved = $approve;
    $donate->save();

    return [
        'status' => true,
        'message' => $approve
            ? 'تمت الموافقة على التبرع.'
            : 'تم رفض التبرع.',
        'data' => $donate,
    ];
     }



}