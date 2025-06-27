<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;   
use Illuminate\Http\Request;
use App\Services\ProfileService;
class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function profile(): JsonResponse
    {
        return response()->json($this->profileService->getProfile());
    }

     public function update(Request $request): JsonResponse
    {
        $result=$this->profileService->updateProfile($request);
        return response()->json($result);
    }
}
