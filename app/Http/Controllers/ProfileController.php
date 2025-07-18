<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;   
use Illuminate\Http\Request;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    $user = Auth::user(); // المستخدم من التوكن

    $result = $this->profileService->updateProfile($request, $user);

    return response()->json($result);
}
    public function uploadImage(Request $request)
{
    return $this->profileService->uploadProfileImage($request);
}

 public function deleteProfileImage(): JsonResponse
    {
        try {
            $result = $this->profileService->deleteProfileImage(Auth::user());
            
            return response()->json([
                'status' => true,
                'message' => 'تم حذف الصورة بنجاح',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

}
