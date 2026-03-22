<?php

namespace App\Actions\Comments;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Rules\Commentable;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\ValidationRules\Rules\Authorized;

class AddComment
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Comment::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('comments/add', static::class);
    }

    public function asController(Request $request): CommentResource
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', new Authorized('update', Project::class)],
            'commentable_type' => ['nullable', 'in:feature,requirement'],
            'commentable_id' => ['nullable', new Commentable('update')],
            'message' => ['required', 'string'],
        ]);

        $comment = $request->user()->comments()->create($validated);

        return new CommentResource($comment);
    }
}
