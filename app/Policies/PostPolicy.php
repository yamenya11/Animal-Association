<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Post $post)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id || $user->hasRole(['admin', 'employee']);
    }

    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id || $user->hasRole(['admin', 'employee']);
    }

    public function approve(User $user, Post $post)
    {
        return $user->hasRole(['admin', 'employee']);
    }

    public function reject(User $user, Post $post)
    {
        return $user->hasRole(['admin', 'employee']);
    }
} 