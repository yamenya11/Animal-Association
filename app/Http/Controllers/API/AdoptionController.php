<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdoptionService;
use Illuminate\Support\Facades\Auth;

class AdoptionController extends Controller
{
       protected $adoptionService;

       public function __construct(AdoptionService $adoptionService)
    {
        $this->adoptionService = $adoptionService;
    }

     public function requestAdoption(Request $request)
    {
        $userId = Auth::id();

        $response = $this->adoptionService->createAdoption($request, $userId);

        if (!$response['status']) {
            return response()->json($response, 400);
        }

        return response()->json($response, 201);
    }
     
    public function myAdoptions()
    {
        $userId = Auth::id();
        $adoptions = $this->adoptionService->getUserAdoptions($userId);

        return response()->json([
            'status' => true,
            'data' => $adoptions,
        ]);
    }
}
