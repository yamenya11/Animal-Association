<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\VolunteerRequest;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    // إدارة المستخدمين
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

    public function approveContent($contentId, ?string $notes = null): array
    {
        $content = Post::findOrFail($contentId);
        $content->status = 'approved';
        $content->notes = $notes;
        $content->save();

        // إرسال إشعار للمستخدم
        $content->user->notify(new PostStatusUpdated($content, 'approved'));

        return [
            'status' => true,
            'message' => 'تمت الموافقة على المحتوى بنجاح',
            'data' => $content
        ];
    }

    public function rejectContent($contentId, ?string $notes = null): array
    {
        $content = Post::findOrFail($contentId);
        $content->status = 'rejected';
        $content->notes = $notes;
        $content->save();

        // إرسال إشعار للمستخدم
        $content->user->notify(new PostStatusUpdated($content, 'rejected'));

        return [
            'status' => true,
            'message' => 'تم رفض المحتوى',
            'data' => $content
        ];
    }

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
} 