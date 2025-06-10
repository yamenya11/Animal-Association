<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VolunteerService;

class VolunteerController extends Controller
{
    protected $volService;

      public function __construct(VolunteerService $volService)
    {
        $this->volService = $volService;
    }

     public function apply(Request $request)
    {
        $response = $this->volService->createRequest($request);
        return response()->json($response, $response['status'] ? 201 : 400);
    }

    public function index()
    {
        return response()->json([
            'status' => true,
            'data'   => $this->volService->listAll(),
        ]);
    }


   // للإدمن: الموافقة / الرفض
    public function respond(Request $request, $id)
    {
       

        $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string',
        ]);
   
 
        $resp = $this->volService->respond(
            $id,
            $request->action,
            $request->notes
        );

        return response()->json($resp);
    }

}
