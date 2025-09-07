<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
  public function forceDelete(User $user, Comment $comment)
{

    return $user->hasRole(['admin', 'employee']);
}

public function edit(User $user, Comment $comment)
{
   
    return $user->id === $comment->user_id;
}

    public function create(User $user)
{
    return true;
}

public function reply(User $user)
{
    return true;
}
}