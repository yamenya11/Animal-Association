<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DonateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Donate;
use App\Services\NotificationService;   

use Illuminate\Support\Facades\Auth;
class DonateController extends Controller
{
    protected $donateService;

    protected $notificationService;
 public function __construct(DonateService $donateService=null, NotificationService $notificationService=null)
{
    $this->donateService = $donateService;
    $this->notificationService = $notificationService;
}

    public function create_donate(Request $request): JsonResponse
{
    try {
        $response = $this->donateService->store($request);

       return response()->json([
            'status' => $response['status'] ?? true,
            'message' => $response['message'],
            'data' => $response['data'],
            'user_id' => $response['data']->user_id // إضافة هذا السطر
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل في إنشاء التبرع: ' . $e->getMessage()
        ], 400);
    }
}
           public function index()
{
    $donations = DB::table('donates')
        ->leftJoin('users', 'donates.user_id', '=', 'users.id')
        ->select(
            'donates.*',
            'users.id as user_id', // تغيير هنا
            'users.name as user_name',
            'users.email as user_email'
        )
        ->orderBy('donates.created_at', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'قائمة التبرعات',
        'data' => $donations
    ]);
}

 public function respond($donateId, Request $request)
{
    $request->validate([
        'is_approved' => 'required|boolean' // تغيير التحقق إلى boolean
    ]);

    $donation = Donate::findOrFail($donateId);
    $isApproved = filter_var($request->input('is_approved'), FILTER_VALIDATE_BOOLEAN);

    // تحديث حالة التبرع
    $donation->update([
        'is_approved' => $isApproved,
        'status' => $isApproved ? 'approved' : 'rejected'
    ]);

    // إرسال الإشعار (إذا كان موجوداً)
    // $this->notificationService->sendDonationStatusNotification($donation, $isApproved ? 'approved' : 'rejected');

    return response()->json([
        'status' => true,
        'message' => $isApproved 
            ? 'تمت الموافقة على التبرع بنجاح' 
            : 'تم رفض التبرع',
        'data' => $donation
    ]);
}




public function approvedDonations()
{
    $userId = Auth::id();

    $donations = Donate::where('user_id', $userId)
                       ->whereIn('is_approved', [true, false])
                       ->orderBy('created_at', 'desc')
                       ->get();

    return response()->json([
        'status' => true,
        'data'   => $donations,
    ]);
}
}
