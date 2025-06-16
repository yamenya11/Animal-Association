<?php
namespace App\Services;

use App\Models\AnimalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AnimalCaseService
{
    public function createCase(Request $request): array
    {
          $validated = $request->validate([
            'name_animal'  => 'required|string|max:255',
            'case_type'  => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'      => 'nullable|image|max:2048', // اختيارية
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('images/photocaseanimal', $fileName, 'public'); // مسار داخل storage/app/public

            $validated['image'] = $path;
        }

         
         $validated['user_id'] = Auth::id();

        $case = AnimalCase::create($validated);

          return [
            'status' => true,
            'message' => 'تم إضافة حالة الحيوان بنجاح',
            'data' => $case,
            'image_url' => asset('storage/' . $case->image)
        ];
    }

     public function getAnimalCasesByUser()
    {
        return AnimalCase::with('animal')
        ->orderBy('created_at', 'desc')
        ->get();

    }

}