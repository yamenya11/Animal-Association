<?php

namespace App\Services;

use App\Models\VolunteerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\VolunteerRequestApproved;
class VolunteerService
{

 public function createRequest(Request $request): array
{
    $validated = $request->validate([
        'full_name'      => 'required|string|max:255',
        'phone'          => 'nullable|string|max:20|regex:/^[0-9]+$/',
        'volunteer_type' => 'required|in:cleaning_shelters,animal_care,photography_and_documentation,design_and_markiting,social_midea_administrator,school_awareness',
        'availability'   => 'nullable|string|max:255',
        'notes'          => 'nullable|string',
        // لا حاجة للتحقق من ملف cv
    ]);

    $filePath = null;

    if ($request->hasFile('cv')) {
        $file = $request->file('cv');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('uploads/cvs', $filename, 'public');
    }

    $volunteer = VolunteerRequest::create([
        'user_id'        => auth()->id(),
        'full_name'      => $validated['full_name'],
        'phone'          => $validated['phone'],
        'volunteer_type' => $validated['volunteer_type'],
        'availability'   => $validated['availability'] ?? null,
        'notes'          => $validated['notes'] ?? null,
        'cv_path'        => $filePath,
        'status'         => 'pending',
    ]);

    // إعداد رابط السيرة الذاتية (إن وجدت فقط)
    $volunteer->cv_url = $filePath ? asset('storage/' . $filePath) : null;

    return [
        'status'  => true,
        'message' => 'انتظر للموافقة.تم إرسال طلب التطوع بنجاح',
        'data'    => $volunteer,
    ];
}



     public function listAll()
    {
         return VolunteerRequest::join('users', 'volunteer_requests.user_id', '=', 'users.id')
        ->select(
            'volunteer_requests.id',
            'volunteer_requests.full_name',
            'volunteer_requests.phone',
            'volunteer_requests.volunteer_type',
            'volunteer_requests.availability',
            'volunteer_requests.notes',
            'volunteer_requests.status',
            'volunteer_requests.created_at',
            'users.name as user_name',
            'users.email as user_email'
        )
        ->orderBy('volunteer_requests.created_at', 'desc')
        ->get();
    }


    public function respond($id, string $action, ?string $notes = null): array
    {
        $vr = VolunteerRequest::findOrFail($id);
       
        if (!in_array($action, ['approved','rejected'])) {
            return [
                'status'  => false,
                'message' => 'إجراء غير صالح.',
            ];
        }
        $vr = VolunteerRequest::find($id);
        $vr->status = 'approved';
        $vr->status = $action;
        $vr->notes  = $notes;
        $vr->save();
        $vr->user->notify(new VolunteerRequestApproved($vr));
        return [
            'status'  => true,
            'message' => $action === 'approved'
                ? 'تمت الموافقة على طلب التطوع.'
                : 'تم رفض طلب التطوع.',
            'data'    => $vr,
        ];
      
    }

    

}