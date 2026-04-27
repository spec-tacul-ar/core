<?php

namespace App\Actions\Comments;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Rules\Authorised;
use App\Rules\Commentable;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class AddComment
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Comment::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('comments/add', static::class)
            ->middleware('sqids:project_id,commentable_id');
    }

    public function asController(Request $request): CommentResource
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', new Authorised('view', Project::class)],
            'commentable_type' => ['nullable', 'in:feature,requirement'],
            'commentable_id' => ['nullable', new Commentable()],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $request->user()->comments()->create($validated);

        return new CommentResource($comment);
    }
}
