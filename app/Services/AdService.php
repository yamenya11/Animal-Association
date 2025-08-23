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
public function __construct(NotificationService $notificationService = null, WalletService $walletService = null)
{
    $this->notificationService = $notificationService;
    $this->walletService = $walletService;
}

public function createAdWithMedia($request)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,avi,mov|max:10240',
    ]);

    $ad = Ad::create([
        'user_id' => Auth::id(),
        'title' => $validated['title'],
        'description' => $validated['description'],
        'price' => $validated['price'],
        'status' => 'pending'
    ]);

    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $path = $file->store('ads_media', 'public');
            
            AdMedia::create([
                'ad_id' => $ad->id,
                'media_path' => $path,
                'media_type' => Str::startsWith($file->getMimeType(), 'image') ? 'image' : 'video'
            ]);
        }
    }

    // هنا نحمّل media مع media_url المخصص
      $ad->load(['media' => function($query) {
        $query->select('*', 
            \DB::raw('CONCAT("' . config('app.url') . '/storage/", media_path) as media_url')
        );
    }]);

    // هنا نعيد $ad بدون load إضافي لأنه تم تحميله بالفعل
    return [
        'status' => true,
        'message' => 'تم إنشاء الإعلان بنجاح',
        'data' => $ad // تم التحميل مسبقاً، لا داعي لـ load('media') again
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

        // $user->notify(new AdApprovedNotification($ad));
    //   $notificationService = app(NotificationService::class);
    //   $notificationService->sendAdApprovedNotification($ad);
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
    $ads = Ad::with(['user', 'media'])
        ->where('status', 'approved')
        ->get()
        ->map(function ($ad) {
            return [
                'id' => $ad->id,
                'title' => $ad->title,
                'description' => $ad->description,
                'price' => $ad->price,
                'user' => [
                    'id' => $ad->user->id,
                    'name' => $ad->user->name,
                    'email' => $ad->user->email,
                ],
                'media' => $ad->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->media_type === 'video' 
                            ? url("/api/stream-video/{$media->id}")
                            : config('app.url') . '/storage/' . $media->media_path,
                        'type' => $media->media_type,
                        'thumbnail' => $media->media_type === 'video'
                            ? config('app.url') . '/storage/video-thumbnails/'.pathinfo($media->media_path, PATHINFO_FILENAME).'.jpg'
                            : config('app.url') . '/storage/' . $media->media_path,
                    ];
                })->toArray()
            ];
        });

    return [
        'status' => true,
        'data' => $ads,
        'message' => 'تم جلب الإعلانات بنجاح'
    ];
}
}