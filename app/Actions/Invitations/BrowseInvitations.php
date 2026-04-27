<?php

namespace App\Actions\Invitations;

use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\Project;
use App\Rules\Authorised;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class BrowseInvitations
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('invitations/browse', static::class)
            ->middleware('sqids:project_id');
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', new Authorised('invite', Project::class)],
        ];
    }

    public function handle(array $validated, string $email)
    {
        return Invitation::query()
            ->when(
                $validated['project_id'] ?? null,
                fn ($query, $value) => $query
                    ->where('project_id', $value)
                    ->with('account'),
                fn ($query) => $query
                    ->where('email', $email)
                    ->with('project'),
            )
            ->get();
    }

    public function asController(ActionRequest $request): ResourceCollection
    {
        return InvitationResource::collection(
            $this->handle($request->validated(), $request->user()->email),
        );
    }
}
