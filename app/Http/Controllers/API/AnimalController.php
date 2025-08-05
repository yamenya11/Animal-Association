<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AnimalService;
class AnimalController extends Controller
{
    
     protected $animalService;

      public function __construct(AnimalService $animalService)
    {
        $this->animalService = $animalService;
    }

      public function available(): JsonResponse
    {
        $animals = $this->animalService->getAvailableAnimals();

        return response()->json([
            'status' => true,
            'data' => $animals,
        ]);
    }

    
}
