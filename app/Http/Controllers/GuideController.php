<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AnimalGuide;
class GuideController extends Controller
{
   public function listAllByCategory()
{
    $categories = Category::with('animalGuides')->get();

    return response()->json([
        'status' => true,
        'data' => $categories->map(function ($category) {
            return [
                'category' => $category->name, // ENGLISH (common, weird, extinct)
                'animals' => $category->animalGuides->map(function ($a) {
                    return [
                        'name' => $a->name,
                        'type' => $a->type,
                        'description' => $a->description,
                        'food' => $a->food,
                        'image' => $a->image ? asset('storage/' . $a->image) : null,
                    ];
                }),
            ];
        }),
    ]);
}


}
