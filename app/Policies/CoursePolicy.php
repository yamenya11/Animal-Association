<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Course $course)
    {
        // المدير يرى كل الكورسات
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // الطبيب البيطري يرى كورساته فقط
        if ($user->hasRole('vet') && $course->doctor_id === $user->id) {
            return true;
        }
        
        // المستخدم العادي يرى الكورسات النشطة فقط
        return $course->is_active;
    }

    public function create(User $user)
    {
        // المدير والطبيب يمكنهم إنشاء كورسات
        return $user->hasAnyRole(['admin', 'vet']);
    }

    public function update(User $user, Course $course)
    {
        // المدير أو الطبيب صاحب الكورس
        return $user->hasRole('admin') || 
               ($user->hasRole('vet') && $course->doctor_id === $user->id);
    }

    public function delete(User $user, Course $course)
    {
        // نفس صلاحية التعديل
        return $this->update($user, $course);
    }
}