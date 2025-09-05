<?php
namespace App\Services;

use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Animal;
class VaccineService
{
public function create(array $validatedData)
{
    if (isset($validatedData['animal_id'])) {
        $animal = Animal::find($validatedData['animal_id']);
        if (!$animal) {
            throw new \Exception('الحيوان غير موجود');
        }
    }

    if (isset($validatedData['image'])) {
        if (!$validatedData['image']->isValid()) {
            throw new \Exception('ملف الصورة غير صالح');
        }
        $validatedData['image'] = $validatedData['image']->store('vaccine_images', 'public');
    }

    $vaccine = Vaccine::create($validatedData);

    if ($vaccine->animal_id) {
        $vaccine->load('animal'); 
    }

    return $vaccine; 
}



    public function list()
    {
        return Vaccine::orderBy('due_date', 'asc')
            ->get()
            ->map(function($vaccine) {
                $vaccineData = $vaccine->toArray();
                if ($vaccine->image) {
                    $vaccineData['image_url'] = config('app.url') . '/storage/' . $vaccine->image;
                }
                return $vaccineData;
            });
    }

    public function dueToday()
    {
        return Vaccine::whereDate('due_date', now()->toDateString())
            ->get()
            ->map(function($vaccine) {
                $vaccineData = $vaccine->toArray();
                if ($vaccine->image) {
                    $vaccineData['image_url'] = config('app.url') . '/storage/' . $vaccine->image;
                }
                return $vaccineData;
            });
    }

    public function updateImage(Request $request, $vaccineId): array
    {
        $vaccine = Vaccine::findOrFail($vaccineId);
        
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($vaccine->image) {
                Storage::disk('public')->delete($vaccine->image);
            }
            
            $vaccine->image = $request->file('image')->store('vaccine_images', 'public');
            $vaccine->save();
        }
        
        // إضافة رابط الصورة للاستجابة
        $vaccineData = $vaccine->toArray();
        if ($vaccine->image) {
            $vaccineData['image_url'] = config('app.url') . '/storage/' . $vaccine->image;
        }
        
        return $vaccineData;
    }

    public function update(array $validatedData, $vaccineId): array
    {
        $vaccine = Vaccine::findOrFail($vaccineId);

        if (isset($validatedData['image']) && $validatedData['image']->isValid()) {
            // Delete old image if exists
            if ($vaccine->image) {
                Storage::disk('public')->delete($vaccine->image);
            }
            
            $validatedData['image'] = $validatedData['image']->store('vaccine_images', 'public');
        } else {
            // Keep the old image if no new image is provided
            unset($validatedData['image']);
        }

        $vaccine->update($validatedData);
        
        // إضافة رابط الصورة للاستجابة
        $vaccineData = $vaccine->fresh()->toArray();
        if ($vaccine->image) {
            $vaccineData['image_url'] = config('app.url') . '/storage/' . $vaccine->image;
        }
        
        return $vaccineData;
    }

    public function delete($vaccineId): bool
    {
        $vaccine = Vaccine::findOrFail($vaccineId);
        
        // Delete associated image if exists
        if ($vaccine->image) {
            Storage::disk('public')->delete($vaccine->image);
        }
        
        return $vaccine->delete();
    }

    public function show($vaccineId): array
    {
        $vaccine = Vaccine::findOrFail($vaccineId);
        
        $vaccineData = $vaccine->toArray();
        if ($vaccine->image) {
            $vaccineData['image_url'] = config('app.url') . '/storage/' . $vaccine->image;
        }
        
        return $vaccineData;
    }
}