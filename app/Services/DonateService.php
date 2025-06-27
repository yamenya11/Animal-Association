<?php

namespace App\Services;
use App\Models\Donate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class DonateService{


 public function store(Request $req): array
{
    $validatedData = $req->validate([
        'full_name' => 'required|string|max:255',
        'number' => 'required|string|max:15',
        'donation_type' => 'required|string|max:15',
        'amount' => 'required|numeric|min:1',
        'notes' => 'nullable|string|max:500',
    ]);

       $validatedData['user_id'] = auth()->id(); // سيتم تعيين null تلقائياً إذا لم يكن مسجلاً

    $donation = Donate::create($validatedData);

    return [
        'status' => true,
        'message' => 'تم تقديم التبرع بنجاح',
        'data' => $donation,
        'user_id' => $donation->user_id // إرجاع user_id الفعلي
    ];
}


      public function respondToPost($donateId, bool $action)
    {
        $donate = Donate::findOrFail($donateId);
        $donate->is_approved = $action; // تصحيح المتغير من $approve إلى $action
        $donate->save();

        return [
            'status' => true,
            'message' => $action
                ? 'تمت الموافقة على التبرع.'
                : 'تم رفض التبرع.',
            'data' => $donate,
        ];
    }



}