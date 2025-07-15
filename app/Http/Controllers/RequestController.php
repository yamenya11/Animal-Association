<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Ad;
use \App\Models\Adoption;
use \App\Models\Donate;
use \App\Models\TemporaryCareRequest;
use Illuminate\Http\JsonResponse; 
class RequestController extends Controller
{

    public function getProcessedRequests(): JsonResponse
{
    $ads = Ad::whereIn('status', ['approved', 'rejected'])
        ->with('user:id,name') // إذا احتجت بيانات المستخدم
        ->get();

    $adoptions = Adoption::whereIn('status', ['approved', 'rejected'])
        ->with(['user:id,name', 'animal:id,name'])
        ->get();

    $donations = Donate::where('is_approved', true)->get();

    $careRequests = TemporaryCareRequest::whereIn('status', ['approved', 'rejected'])
        ->with(['user:id,name', 'animal:id,name'])
        ->get();

    return response()->json([
        'status' => true,
        'data' => [
            'ads' => $ads,
            'adoptions' => $adoptions,
            'donations' => $donations,
            'temporary_care_requests' => $careRequests,
        ]
    ]);
}

}
