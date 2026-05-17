<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Comment;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function create(Account $account): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the Account can view the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Comment $comment): Response
    {
        return $account->canView($comment, 'comments')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Comment $comment): Response
    {
        if ($this->view($account, $comment)->denied()) {
            return Response::denyAsNotFound();
        }

        if ($comment->project->isArchived()) {
            return Response::deny();
        }

        return $comment->authorIs($account)
            ? Response::allow()
            : Response::deny();
    }
}
