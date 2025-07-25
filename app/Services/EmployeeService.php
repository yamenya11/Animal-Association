<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\VolunteerRequest;
use App\Models\Message;
use App\Models\Animal;
use App\Notifications\PostStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    // إدارة المستخدمين
    //عرضس
    public function getAllUsers()
    {
        return User::select([
            'id',
            'name',
            'email',
            'created_at',
            'wallet_balance',
            'experience',
            'region'
        ])->get();
    }

    // إدارة المحتوى
    public function getPendingContent()
    {
        return Post::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();
    }

    // public function approveContent($contentId, ?string $notes = null): array
    // {
    //     $content = Post::findOrFail($contentId);
    //     $content->status = 'approved';
    //     $content->notes = $notes;
    //     $content->save();

    //     // إرسال إشعار للمستخدم
    //     $content->user->notify(new PostStatusUpdated($content, 'approved'));

    //     return [
    //         'status' => true,
    //         'message' => 'تمت الموافقة على المحتوى بنجاح',
    //         'data' => $content
    //     ];
    // }

    // public function rejectContent($contentId, ?string $notes = null): array
    // {
    //     $content = Post::findOrFail($contentId);
    //     $content->status = 'rejected';
    //     $content->notes = $notes;
    //     $content->save();

    //     // إرسال إشعار للمستخدم
    //     $content->user->notify(new PostStatusUpdated($content, 'rejected'));

    //     return [
    //         'status' => true,
    //         'message' => 'تم رفض المحتوى',
    //         'data' => $content
    //     ];
    // }

    // التقارير
    public function generateDailyReport()
    {
        $today = now()->format('Y-m-d');

        $report = [
            'new_users' => User::whereDate('created_at', $today)->count(),
            'pending_content' => Post::where('status', 'pending')->count(),
            'approved_content' => Post::whereDate('updated_at', $today)
                ->where('status', 'approved')
                ->count(),
            'rejected_content' => Post::whereDate('updated_at', $today)
                ->where('status', 'rejected')
                ->count(),
            'active_volunteers' => VolunteerRequest::where('status', 'approved')->count()
        ];

        return $report;
    }

    // التواصل مع المتطوعين
    public function getActiveVolunteers()
    {
        return VolunteerRequest::where('status', 'approved')
            ->with('user')
            ->get();
    }

    public function sendMessageToVolunteer($volunteerId, string $message): array
    {
        $volunteer = VolunteerRequest::findOrFail($volunteerId);

        if ($volunteer->status !== 'approved') {
            return [
                'status' => false,
                'message' => 'هذا المتطوع غير نشط'
            ];
        }

        $message = Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id' => $volunteer->user_id,
            'message' => $message
        ]);

        return [
            'status' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data' => $message
        ];
    }

    // إدارة الحيوانات
    public function getAllAnimals()
    {
        return Animal::with('user')
            ->latest()
            ->get();
    }

    public function getAnimal($animalId)
    {
        return Animal::with('user')->findOrFail($animalId);
    }

    public function createAnimal(array $data): array
    {
        try {
            DB::beginTransaction();

            $animal = Animal::create([
                'user_id' => auth()->id(),
                'name' => $data['name'],
                'type' => $data['type'],
                'age' => $data['age'] ?? null,
                'health_info' => $data['health_info'] ?? null,
                'image' => $data['image'] ?? null,
                'is_adopted' => false
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'تم إضافة الحيوان بنجاح',
                'data' => $animal
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء إضافة الحيوان',
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateAnimal($animalId, array $data): array
    {
        try {
            DB::beginTransaction();

            $animal = Animal::findOrFail($animalId);
            
            // التحقق من أن المستخدم هو صاحب الحيوان
            if ($animal->user_id !== auth()->id()) {
                return [
                    'status' => false,
                    'message' => 'غير مصرح لك بتعديل هذا الحيوان'
                ];
            }

            $animal->update([
                'name' => $data['name'] ?? $animal->name,
                'type' => $data['type'] ?? $animal->type,
                'age' => $data['age'] ?? $animal->age,
                'health_info' => $data['health_info'] ?? $animal->health_info,
                'image' => $data['image'] ?? $animal->image
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'تم تحديث معلومات الحيوان بنجاح',
                'data' => $animal
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث معلومات الحيوان',
                'error' => $e->getMessage()
            ];
        }
    }

    public function deleteAnimal($animalId): array
    {
        try {
            DB::beginTransaction();

            $animal = Animal::findOrFail($animalId);
            
            // التحقق من أن المستخدم هو صاحب الحيوان
            if ($animal->user_id !== auth()->id()) {
                return [
                    'status' => false,
                    'message' => 'غير مصرح لك بحذف هذا الحيوان'
                ];
            }

            // حذف الصورة إذا وجدت
            if ($animal->image) {
                Storage::delete($animal->image);
            }

            $animal->delete();

            DB::commit();

            return [
                'status' => true,
                'message' => 'تم حذف الحيوان بنجاح'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف الحيوان',
                'error' => $e->getMessage()
            ];
        }
    }

    public function uploadAnimalImage($request): array
    {
        try {
            if (!$request->hasFile('image')) {
                return [
                    'status' => false,
                    'message' => 'لم يتم اختيار صورة'
                ];
            }

            $file = $request->file('image');
            $path = $file->store('animals', 'public');

            return [
                'status' => true,
                'message' => 'تم رفع الصورة بنجاح',
                'data' => [
                    'path' => $path,
                    'url' => Storage::url($path)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء رفع الصورة',
                'error' => $e->getMessage()
            ];
        }
    }
} 