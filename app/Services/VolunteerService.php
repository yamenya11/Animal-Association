<?php

namespace App\Services;

use App\Models\VolunteerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\VolunteerRequestApproved;
use \App\Models\VolunteerType;
class VolunteerService
{

public function createRequest(Request $request): array
{
    $validated = $request->validate([
        'full_name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20|regex:/^[0-9]+$/',
        'volunteer_type_name' => 'required|string|max:255', // استخدام الاسم بدلاً من slug
        'availability' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ]);

    // البحث عن النوع بالاسم (بدون حساسية لحالة الأحرف)
    $volunteerType = VolunteerType::where('name_en', $validated['volunteer_type_name'])
                        ->orWhere('name_en', $validated['volunteer_type_name'])
                        ->first();

    if (!$volunteerType) {
        return [
            'status' => false,
            'message' => 'نوع التطوع غير موجود'
        ];
    }

    // معالجة ملف السيرة الذاتية
    $filePath = null;
    if ($request->hasFile('cv')) {
        $file = $request->file('cv');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('uploads/cvs', $filename, 'public');
    }

    // إنشاء طلب التطوع
    $volunteer = VolunteerRequest::create([
        'user_id' => auth()->id(),
        'full_name' => $validated['full_name'],
        'phone' => $validated['phone'],
        'volunteer_type_id' => $volunteerType->id,
        'availability' => $validated['availability'] ?? null,
        'notes' => $validated['notes'] ?? null,
        'cv_path' => $filePath,
        'status' => 'pending',
    ]);

    return [
        'status' => true,
        'message' => 'تم إرسال طلب التطوع بنجاح',
        'data' => [
            'request' => $volunteer,
            'volunteer_type' => $volunteerType
        ]
    ];
}



public function listAll(Request $request = null)
{
    $query = VolunteerRequest::with(['user', 'type']);

    if ($request && $request->has('type_slug')) {
        $query->whereHas('type', function($q) use ($request) {
            $q->where('slug', $request->type_slug);
        });
    }

    return $query->orderBy('created_at', 'desc')->get();
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