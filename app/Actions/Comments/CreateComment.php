<?php

namespace App\Actions\Comments;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\CommentResource;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Project;
use App\Rules\Authorised;
use App\Rules\Commentable;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateComment
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Comment::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('comments', static::class)
            ->middleware('sqids:project_id,commentable_id');
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorised('comment', Project::class)],
            'commentable_type' => ['nullable', 'in:feature,requirement'],
            'commentable_id' => ['nullable', 'integer', new Commentable()],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function handle(Account $account, array $data): Comment
    {
        return $account->comments()->create($data);
    }

    public function asController(ActionRequest $request): CommentResource
    {
        $comment = $this->handle($request->user(), $request->validated());

        return new CommentResource($comment);
    }
}
