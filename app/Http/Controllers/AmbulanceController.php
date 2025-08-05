<?php

namespace App\Http\Controllers;

use App\Models\Ambulance;
use Illuminate\Http\Request;
class AmbulanceController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Ambulance::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            
            'driver_name' => 'required',
            'driver_phone' => 'required'
        ]);

        $ambulance = Ambulance::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة سيارة الإسعاف بنجاح',
            'data' => $ambulance
        ], 201);
    }
    // app/Http/Controllers/AmbulanceController.php
public function update(Request $request, Ambulance $ambulance)
{
    $validated = $request->validate([
        'driver_name' => 'sometimes|required',
        'driver_phone' => 'sometimes|required',
        'status' => 'sometimes|required|in:available,on_mission,maintenance'
    ]);

    $ambulance->update($validated);

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث بيانات سيارة الإسعاف بنجاح',
        'data' => $ambulance
    ]);
}

public function destroy(Ambulance $ambulance)
{
    try {
        $ambulance->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'تم حذف سيارة الإسعاف بنجاح'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل في حذف سيارة الإسعاف: ' . $e->getMessage()
        ], 500);
    }
}
}
