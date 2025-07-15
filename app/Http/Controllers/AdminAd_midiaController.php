<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Services\AdService;
use Illuminate\Support\Facades\DB;
class AdminAd_midiaController extends Controller
{
     protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

public function index() 
{
    $ads = Ad::with(['user:id,name,email', 'media'])
        ->where('status', 'pending')
        ->get()
        ->map(function ($ad) {
            $mediaArray = $ad->media->map(function ($media) {
                return (object)[
                    'path' => asset('storage/' . $media->media_path),
                    'type' => $media->media_type
                ];
            })->toArray();
            
            return [
                'id' => $ad->id,
                'title' => $ad->title,
                'description' => $ad->description,
                'status' => $ad->status,
                'user' => (object)[
                    'name' => $ad->user->name,
                    'email' => $ad->user->email
                ],
                'media' => $mediaArray
            ];
        });

      return response()->json([
        'status' => true,
        'data' => $ads
    ]);
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
