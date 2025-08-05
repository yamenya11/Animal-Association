<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    // الصلاحيات الأساسية
    public function create(User $user) {
        return true; // الجميع يمكنهم النشر
    }

    public function update(User $user, Post $post) {
        return $user->id === $post->user_id || $user->hasRole(['admin', 'employee']);
    }

    public function forceDelete(User $user, Post $post) {
        return $user->id === $post->user_id || $user->hasRole(['admin', 'employee']);
    }

    // صلاحيات إدارية
    public function manageAll(User $user) {
        return $user->hasRole(['admin', 'employee']);
    }
}