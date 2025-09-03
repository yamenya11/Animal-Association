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
        'full_name'      => 'required|string|max:255',
        'number'         => 'required|string|max:15',
        'donation_type'  => 'required|string|max:15',
        'notes'          => 'nullable|string|max:500',
        'ammountinkello' => 'required|string|max:15',
    ]);

    $validatedData['user_id'] = auth()->id();
    $validatedData['status']  = 'pending'; // ← الحالة الافتراضية عند الإنشاء

    $donation = Donate::create($validatedData);

    return [
        'status'  => true,
        'message' => 'تم تقديم التبرع بنجاح وسيتم مراجعته من قبل الإدارة',
        'data'    => $donation,
    ];
}


public function respondToPost($donateId, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $donate = Donate::findOrFail($donateId);
        $donate->update(['status' => $validated['status']]);
 
            $donate->user->notify(new \App\Notifications\DonationStatusNotification($donate, $validated['status']));
        return response()->json([
            'status'  => true,
            'message' => $validated['status'] === 'approved'
                ? 'تمت الموافقة على التبرع.'
                : 'تم رفض التبرع.',
            'data'    => $donate,
        ]);
    }


}