<?php

namespace App\Actions\Invitations;

use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\Project;
use App\Rules\IsMe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\ValidationRules\Rules\Authorized;

class BrowseInvitations
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('invitations/browse', static::class);
    }

    public function asController(Request $request): ResourceCollection
    {
        $validated = $request->validate([
            'project_id' => ['exists:projects,id', new Authorized('update', Project::class)],
        ]);

        $invitations = Invitation::query()
            ->when(
                $validated['project_id'] ?? null,
                fn ($query, $value) => $query
                    ->where('project_id', $value)
                    ->with('account'),
                fn ($query) => $query
                    ->where('email', $request->user()->email)
                    ->with('project'),
            )
            ->get();

        return InvitationResource::collection($invitations);
    }
}
