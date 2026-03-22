<?php

namespace App\Actions\Comments;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Comment;

class DeleteComment
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('comment'));
    }

    public static function routes(Router $router): void
    {
        $router->post('comments/{comment}/delete', static::class);
    }

    public function handle(Comment $comment): void
    {
        $comment->delete();
    }

    public function asController(Comment $comment): Response
    {
        $this->handle($comment);

        return response()->noContent();
    }
}
