<?php

namespace App\Services;

use App\Models\User;
use App\Models\Donate;
use App\Models\Adoption;
use App\Models\AnimalCase;
use App\Models\Ad;
use App\Models\TemporaryCareRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class ProfileService
{
public function getProfile(): array
{
    $user = Auth::user();

    if (!$user) {
        return [
            'status' => false,
            'message' => 'المستخدم غير موجود',
        ];
    }

    // الحسابات
    $donationsCount = $user->donate()->count();
    $adoptionsCount = $user->adoptions()->count();
    $urgentCasesCount = $user->animal_cases()->count();
    $adsCount = $user->ads()->count();
    $temporaryCareCount = $user->temporary()->count();

    $activityScore = $donationsCount + $adoptionsCount + $urgentCasesCount + $adsCount + $temporaryCareCount;
    $level = $this->calculateLevel($activityScore);

    if ($user->level !== $level) {
        $user->level = $level;
        $user->save();
    }

    $profile = [
        'user_name'      => $user->name,
        'user_email'     => $user->email,
        'wallet_balance' => $user->wallet_balance,
        'level'          => $user->level,
        'phone'          => $user->phone,
        'address'        => $user->address,
        'profile_image'  => $user->profile_image ? config('app.url') . '/storage/' . $user->profile_image : null,
        'donation_count' => $donationsCount,
        'adoption_count' => $adoptionsCount,
        'urgent_cases_count' => $urgentCasesCount,
        'ads_count' => $adsCount,
        'temporaryCareCount' => $temporaryCareCount,
    ];

    return [
        'status' => true,
        'data'   => $profile,
    ];
}

    private function calculateLevel(int $score): string
{
    return match (true) {
        $score < 5      => '1',
        $score < 15     => '2',
        $score < 30     => '3',
       $score < 45     => '2.5',
        $score < 46     => '4',
        $score < 50     => '5',
     default         => '0',
    };
}

public function uploadProfileImage(Request $request): array
{
    $user = Auth::user();

    $request->validate([
        'profile_image' => 'required|image|max:2048',
    ]);

    $filename = time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
    $path = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');

    $user->profile_image = $path;
    $user->save();

    return [
        'status' => true,
        'message' => 'تم رفع الصورة بنجاح',
        'profile_image_url' => config('app.url') . '/storage/' . $path
    ];
}
public function deleteProfileImage(User $user): array
    {
        if (empty($user->profile_image)) {
            throw new \Exception('لا توجد صورة ملخصة للحذف');
        }

        $storagePath = str_replace('storage/', '', $user->profile_image);
        
        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }

        $user->profile_image = null;
        $user->save();

        return [
            'deleted' => true,
            'image_path' => null
        ];
    }


public function updateProfile(Request $request)
{
    $user = Auth::user();
    DB::beginTransaction();

    try {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:15|unique:users,phone,' . $user->id,
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->has('name')) $user->name = $request->input('name');
        if ($request->has('address')) $user->address = $request->input('address');
        if ($request->has('phone')) $user->phone = $request->input('phone');

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $path;
        }

        $user->save();
        DB::commit();

        return [
            'status' => true,
            'message' => 'تم التحديث بنجاح',
            'data' => [
                'name' => $user->name,
                'address' => $user->address,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image 
                    ? config('app.url') . '/storage/' . $user->profile_image
                    : null,
            ]
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        return [
            'status' => false,
            'message' => 'حدث خطأ أثناء التحديث',
            'error' => $e->getMessage()
        ];
    }
}



}
