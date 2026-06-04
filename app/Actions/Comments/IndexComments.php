<?php

namespace App\Actions\Comments;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Rules\Authorised;

class IndexComments
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('comments', static::class)
            ->middleware('sqids:project_id');
    }

    public function rules(): array
    {
        // TODO: project_id shouldn't be required when we filter by commentable

        return [
            'project_id' => ['required', 'integer', new Authorised('view', Project::class)],
        ];
    }

    public function handle(array $data)
    {
        // TODO Might have broken this ripping the draft stuff out.
        // TODO Paginate

        $comments = Comment::query()
            ->where('project_id', $data['project_id'])
            ->with('account', 'commentable')
            ->orderBy('created_at', 'desc')
            ->get();

        return $comments;
    }

    public function asController(ActionRequest $request): ResourceCollection
    {
        $comments = $this->handle($request->validated());

        return CommentResource::collection($comments);
    }
}
