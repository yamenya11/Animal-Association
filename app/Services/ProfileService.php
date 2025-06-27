<?php

namespace App\Services;

use App\Models\User;
use App\Models\Donate;
use App\Models\Adoption;
use App\Models\AnimalCase;
use Illuminate\Support\Facades\Auth;

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

        $profile = [
            'user_name'      => $user->name,
            'user_email'     => $user->email,
            'wallet_balance' => $user->wallet_balance,
            'experience'     => $user->experience,
            'region'         => $user->region,
            'profile_image'  => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'donation_count' => $user->donate()->count(),
            'adoption_count' => $user->adoptions()->count(),
            'urgent_cases_count' => $user->animal_cases()->count(),
        ];

        return [
            'status' => true,
            'data'   => $profile,
        ];
    }

    public function updateProfile(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'sometimes|string|max:255',
        'profile_image' => 'sometimes|image|max:2048',
    ]);

    if ($request->hasFile('profile_image')) {
        $filename = time() . '.' . $request->profile_image->extension();
        $path = $request->profile_image->storeAs('profile_images', $filename, 'public');
        $user->profile_image = $path;
    }

    if ($request->name) {
        $user->name = $request->name;
    }

    $user->save();

    return response()->json(['status' => true, 'message' => 'تم تحديث البروفايل']);
}




}
