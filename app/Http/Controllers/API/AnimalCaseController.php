<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnimalCaseService;
class AnimalCaseController extends Controller
{
 protected $animalCaseService;

    public function __construct(AnimalCaseService $animalCaseService)
    {
        $this->animalCaseService = $animalCaseService;
    }

     public function store(Request $request)
    {
        $response = $this->animalCaseService->createCase($request);

        return response()->json($response, $response['status'] ? 201 : 400);
    }


}
