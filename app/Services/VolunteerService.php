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
            'cv' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

             $filePath = null;

            if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/uploads/cvs', $filename);
    }

      $volunteer =VolunteerRequest::create([
        'user_id' => auth()->id(),
        'cv_path' => $filePath,
        'status' => 'pending',
    ]);


     return [
        'status' => true,
        'message' => 'تم إرسال طلب التطوع بنجاح',
        'data' => $volunteer,
           ];

    }


     public function listAll()
    {
         return VolunteerRequest::join('users', 'volunteer_requests.user_id', '=', 'users.id')
        ->select(
            'volunteer_requests.id',
            'volunteer_requests.cv_path',
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