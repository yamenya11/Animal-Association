<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdService;
class AdController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    public function store(Request $request)
    {
        $result = $this->adService->createAdWithMedia($request);
        return response()->json($result);
    }
    public function show_All_Ads(Request $request){
     
        $result = $this->adService->getAllAds_for_user($request);
        return response()->json($result);


    }

}
