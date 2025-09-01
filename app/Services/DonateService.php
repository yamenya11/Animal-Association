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
        'notes' => 'nullable|string|max:500',
        'ammountinkello' => 'required|string|max:15',
    ]);

    $validatedData['user_id'] = auth()->id();
    $validatedData['is_approved'] = false; // ← وضعية الانتظار

    $donation = Donate::create($validatedData);

    return [
        'status' => true,
        'message' => 'تم تقديم التبرع بنجاح وسيتم مراجعته من قبل الإدارة',
        'data' => $donation,
        'is_approved' => false // ← تأكيد أنها قيد الانتظار
    ];
}


 public function respondToPost($donateId, Request $request)
{
    $request->validate([
        'is_approved' => 'required|boolean'
    ]);

    $donate = Donate::findOrFail($donateId);
    $isApproved = filter_var($request->input('is_approved'), FILTER_VALIDATE_BOOLEAN);

    $donate->update([
        'is_approved' => $isApproved
    ]);

    return response()->json([
        'status' => true,
        'message' => $isApproved
            ? 'تمت الموافقة على التبرع.'
            : 'تم رفض التبرع.',
        'data' => $donate,
        'is_approved' => $isApproved // ← إرجاع الحالة الجديدة
    ]);
}



}