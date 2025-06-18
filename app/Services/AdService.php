<?php
namespace App\Services;

use App\Models\Ad;
use App\Models\AdMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
use Stephenjude\Wallet\Exceptions\InsufficientFundException;
use App\Notifications\AdApprovedNotification;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
class AdService
{

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
{
    $this->notificationService = $notificationService;
}
     public function createAdWithMedia($request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,mp4,avi,mov|max:10240', // max 10MB
        ]);


          $user = Auth::user();

      
         $ad = Ad::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
        ]);
       
        foreach ($request->file('media') as $file) {
           $filename = uniqid() . '.' . $file->getClientOriginalExtension();
           $path = $file->storeAs('ads_media', $filename, 'public'); // استخدم مسار واحد فقط

            $mime = $file->getMimeType();
          $type = str_starts_with($mime, 'image') ? 'image' : 'video';

            AdMedia::create([
                'ad_id' => $ad->id,
                'media_path' => $path,
                'media_type' => $type,
            ]);
        }

        return [
            'status' => true,
            'message' => 'تم إنشاء الإعلان بنجاح.',
            'data' => $ad->load('media'),
        ];
    }

    public function approveAd($adId, $adminId)
{
    $ad = Ad::with('user')->findOrFail($adId);

    if ($ad->status !== 'pending') {
        return response()->json([
            'status' => false,
            'message' => 'تمت معالجة هذا الإعلان مسبقاً.'
        ], 400);
    }

    $user = $ad->user;

    DB::beginTransaction();
    try {
        // 1. خصم المبلغ
        $user->withdraw($ad->price, 'خصم مقابل إعلان #' . $ad->id);

        // 2. تحديث حالة الإعلان
        $ad->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now()
        ]);

        // 3. إرسال الإشعارات
        $this->notificationService->sendAdApprovedNotification($ad);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'تمت الموافقة على الإعلان بنجاح',
            'data' => $ad->fresh()
        ]);

    } catch (InsufficientFundException $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'لا يوجد رصيد كافٍ للموافقة على الإعلان.',
            'required' => $ad->price,
            'current_balance' => $user->balance()
        ], 400);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء الموافقة على الإعلان: ' . $e->getMessage()
        ], 500);
    }
}



     public function getAllAds_for_user()
   {
    return Ad::select([
            'ads.id',
            'ads.title',
            'ads.description',
            'ads.status',
            'users.name as name_usre',
            'users.email as email_user',
            'ad_media.media_path',
            'ad_media.media_type',
        ])
        ->leftJoin('users', 'ads.user_id', '=', 'users.id')
        ->leftJoin('ad_media', 'ads.user_id', '=', 'ad_media.id')
        ->where('ads.status', 'approved')
        ->get();
      }

}