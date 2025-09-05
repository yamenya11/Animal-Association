<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AnimalGuide;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class GuideController extends Controller
{
  
   public function listAllByCategory()
{
    $categories = Category::with('animalGuides')->get();

    return response()->json([
        'status' => true,
        'data' => $categories->map(function ($category) {
            return [
                'category' => $category->name,
                'animals' => $category->animalGuides->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'name' => $a->name,
                        'type' => $a->type,
                        'description' => $a->description,
                        'food' => $a->food,
                        'image_url' => $a->image ? config('app.url') . '/storage/' . $a->image : null, // ✅ التعديل هنا
                    ];
                }),
            ];
        }),
    ]);
}

  
 public function createGuide(Request $request): JsonResponse
{
    if (!auth()->user()->hasAnyRole(['admin', 'employee', 'vet'])) {
        return response()->json([
            'status' => false,
            'message' => 'غير مصرح لك بهذا الإجراء'
        ], 403);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'description' => 'required|string',
        'food' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('animal_guides', 'public');
        $validated['image'] = $path;
    }

    $animalGuide = AnimalGuide::create($validated);

    $animalGuide->image_url = $animalGuide->image ? config('app.url') . '/storage/' . $animalGuide->image : null;

    return response()->json([
        'status' => true,
        'message' => 'تم إنشاء دليل الحيوان بنجاح',
        'data' => $animalGuide
    ], 201);
}
  
 public function updateGuide(Request $request, $id): JsonResponse
{
    if (!auth()->user()->hasAnyRole(['admin', 'employee', 'vet'])) {
        return response()->json([
            'status' => false,
            'message' => 'غير مصرح لك بهذا الإجراء'
        ], 403);
    }

    $animalGuide = AnimalGuide::findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'type' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'food' => 'sometimes|string',
        'category_id' => 'sometimes|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($request->hasFile('image')) {
        if ($animalGuide->image) {
            Storage::disk('public')->delete($animalGuide->image);
        }
        
        $path = $request->file('image')->store('animal_guides', 'public');
        $validated['image'] = $path;
    }

    $animalGuide->update($validated);

    $animalGuide->image_url = $animalGuide->image ? config('app.url') . '/storage/' . $animalGuide->image : null;

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث دليل الحيوان بنجاح',
        'data' => $animalGuide
    ]);
}


    public function deleteGuide($id): JsonResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'employee', 'vet'])) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بهذا الإجراء'
            ], 403);
        }

        $animalGuide = AnimalGuide::findOrFail($id);

        if ($animalGuide->image) {
            Storage::disk('public')->delete($animalGuide->image);
        }

        $animalGuide->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف دليل الحيوان بنجاح'
        ]);
    }


    public function showGuide($id): JsonResponse
{
    $animalGuide = AnimalGuide::with('category')->findOrFail($id);

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $animalGuide->id,
            'name' => $animalGuide->name,
            'type' => $animalGuide->type,
            'description' => $animalGuide->description,
            'food' => $animalGuide->food,
            'image_url' => $animalGuide->image ? config('app.url') . '/storage/' . $animalGuide->image : null, // ✅ التعديل هنا
            'category' => $animalGuide->category->name
        ]
    ]);
}

}