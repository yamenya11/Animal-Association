<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
  public function forceDelete(User $user, Comment $comment)
{
    // الأدمن والموظف فقط يمكنهم الحذف النهائي
    return $user->hasRole(['admin', 'employee']);
}

public function edit(User $user, Comment $comment)
{
    // صاحب التعليق فقط يمكنه التعديل
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