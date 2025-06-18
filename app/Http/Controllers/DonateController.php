<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DonateService;
class DonateController extends Controller
{
    protected $donateService;

    
        public function __construct(DonateService $donateService)
         {
         $this->donateService = $donateService;
         }

        public function create_donate(Request $request)
        {
            $response = $this->donateService->store($request);
            return response()->json($response, $response['status'] ? 201 : 400);
        }

        // DonateController
        public function respond($donateId, Request $request)
        {
            $approve = $request->input('approve', true); // true = موافقة، false = رفض
            $response = $this->donateService->respondToDonate($donateId, $approve);

            return response()->json($response);
        }

}
