<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function create(mixed $account): bool
    {
        return true;
    }

    /**
     * Determine whether the Account can view the model.
     *
     * @param  mixed  $account
     * @param  mixed  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, mixed $comment)
    {
        return $account->canView($comment, 'comments');
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  mixed  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, mixed $comment)
    {
        return $account->canView($comment, 'comments')
            && ($account->owns($comment, 'comments') || $comment->authorIs($account));
    }
}
