<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
    $response = $this->authService->register($request);
    return response()->json($response, 201);
    }

    public function login(Request $request)
{
    $response = $this->authService->login($request);
    return response()->json($response, 200);
}
   public function logout(Request $request)
{
    $response = $this->authService->logout($request);
    return response()->json($response, 200);
}

 // عرض ملف المستخدم
    public function profile()
    {
        $response = $this->authService->profile();
        return response()->json($response);
    }
}
