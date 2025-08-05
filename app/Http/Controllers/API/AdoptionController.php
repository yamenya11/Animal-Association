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
        return response()->json([
            'status' => false,
            'message' => $response['message'] ?? 'فشل في إنشاء طلب التبني',
            'errors' => $response['errors'] ?? null,
            'code' => 400
        ], 400);
    }

    return response()->json([
        'status' => true,
        'message' => 'تم إنشاء طلب التبني بنجاح',
        'data' => $response['data'] ?? null,
        'code' => 201
    ], 201);
}
     
    public function myAdoptions()
    {
        $userId = Auth::id();
        $adoptions = $this->adoptionService->getUserAdoptions();

        return response()->json([
            
            'status' => true,
            'data' => $adoptions,
        ]);
    }
}
