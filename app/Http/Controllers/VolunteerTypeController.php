<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VolunteerTypeService;

class VolunteerTypeController extends Controller
{
        protected $typeService;

    public function __construct(VolunteerTypeService $typeService)
    {
        $this->typeService = $typeService;
    }

    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => $this->typeService->getAllTypes()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'slug' => 'required|string|unique:volunteer_types',
            'description' => 'nullable|string'
        ]);

        $type = $this->typeService->createType($validated);

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء القسم بنجاح',
            'data' => $type
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
          
            'name_en' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:volunteer_types,slug,'.$id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean'
        ]);

        $type = $this->typeService->updateType($id, $validated);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث القسم بنجاح',
            'data' => $type
        ]);
    }

    public function destroy($id)
    {
        $this->typeService->deleteType($id);

        return response()->json([
            'status' => true,
            'message' => 'تم حذف القسم بنجاح'
        ], 200);
    }

    public function show($id)
{
    $type = $this->typeService->getTypeById($id);
    
    return response()->json([
        'status' => true,
        'data' => $type
    ]);
}

public function indexWithCount()
{
    $result = $this->typeService->getTypesWithVolunteersCount();
    
    return response()->json([
        'status' => $result['success'],
        'message' => $result['message'],
        'data' => $result['data']
    ], $result['success'] ? 200 : 404);
}

public function showVolunteers($typeId)
{
    $volunteerData = $this->typeService->getVolunteersByType($typeId);

    return response()->json([
        'status' => true,
        'data' => $volunteerData
    ]);
}

}
