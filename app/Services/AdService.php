<?php
namespace App\Services;

use App\Models\Ad;
use App\Models\AdMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Str;
use Stephenjude\Wallet\Exceptions\InsufficientFundException;
use App\Notifications\AdApprovedNotification;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use App\Services\WalletService;
class AdService
{

    protected $notificationService;
protected $walletService;
    public function __construct(NotificationService $notificationService,WalletService $walletService)
{
    $this->notificationService = $notificationService;
      $this->walletService = $walletService;
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

    // إنشاء الإعلان
    $ad = Ad::create([
        'user_id' => $user->id,
        'title' => $request->title,
        'description' => $request->description,
        'price' => $request->price,
    ]);

    // رفع الميديا
    foreach ($request->file('media') as $file) {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('ads_media', $filename, 'public'); // تخزين في public/ads_media

        $mime = $file->getMimeType();
        $type = str_starts_with($mime, 'image') ? 'image' : 'video';

        // استخراج رابط العرض للواجهة
        $url = Storage::url($path);

        AdMedia::create([
            'ad_id' => $ad->id,
            'media_path' => $path, // نخزن المسار الحقيقي
            'media_type' => $type,
        ]);
    }

    // تحميل العلاقة media
    $ad->load('media');

    return [
        'status' => true,
        'message' => 'تم إنشاء الإعلان بنجاح.',
        'data' => [
            'id' => $ad->id,
            'title' => $ad->title,
            'description' => $ad->description,
            'price' => $ad->price,
            'media' => $ad->media->map(function ($media) {
                return [
                    'type' => $media->media_type,
                    'url' => asset('storage/' . $media->media_path),
                ];
            }),
        ],
    ];
}


public function approveAd($adId, $adminId)
{
    DB::beginTransaction();
    
    try {
        $ad = Ad::with('user')->findOrFail($adId);
        $user = $ad->user;
        $amount = (float) $ad->price;

        \Log::info('Attempting to withdraw', [
            'user_id' => $user->id,
            'current_balance' => $user->wallet_balance,
            'amount' => $amount
        ]);

        if ((float) $user->wallet_balance < $amount) {
            throw new \Exception('رصيد غير كافي');
        }

        // Pass the ad to the withdraw method
        $this->walletService->withdraw($user, $amount, $ad);

        $ad->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        $user->notify(new AdApprovedNotification($ad));

        DB::commit();

        return [
            'status' => true,
            'message' => 'تمت الموافقة على الإعلان بنجاح',
            'ad' => $ad->fresh()
        ];

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Approval failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'status' => false,
            'message' => 'فشل في الموافقة على الإعلان: ' . $e->getMessage()
        ];
    }
}


     public function getAllAds_for_user()
   {
    return Ad::select([
            'ads.id',
            'ads.title',
            'ads.description',
            'ads.status',
            'users.id',
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