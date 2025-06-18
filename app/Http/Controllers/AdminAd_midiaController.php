<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Services\AdService;
class AdminAd_midiaController extends Controller
{
     protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    public function index()
{
    return Ad::select([
        'ads.id',
        'ads.title',
        'ads.description',
        'ads.status',
        'users.name as name_user',
        'users.email as email_user',
        'ad_media.media_path',
        'ad_media.media_type',
    ])
    ->leftJoin('users', 'ads.user_id', '=', 'users.id')
    ->leftJoin('ad_media', 'ads.id', '=', 'ad_media.ad_id') // ✅ هذا هو التعديل المهم
    ->where('ads.status', 'pending')
    ->get();
}


     public function respond(Request $request, $adId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        if ($request->status === 'approved') {
            $result = $this->adService->approveAd($adId, auth()->id());
        } else {
            $ad = Ad::findOrFail($adId);
            $ad->status = 'rejected';
            $ad->approved_by = auth()->id();
            $ad->approved_at = now();
            $ad->save();

            $result = [
                'status' => true,
                'message' => 'تم رفض الإعلان.',
                'data' => $ad,
            ];
        }

        return response()->json($result);
    }
}
