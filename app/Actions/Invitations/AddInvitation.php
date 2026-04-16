<?php

namespace App\Actions\Invitations;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\Role;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\Project;
use App\Rules\InvitationEmail;
use App\Rules\NotOwnEmail;
use Spatie\ValidationRules\Rules\Authorized;

class AddInvitation
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('invitations/add', static::class);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Invitation::class);
    }

    public function asController(Request $request): InvitationResource
    {
        $validated = Validator::make($request->all(), [
            'project_id' => ['required', 'integer', new Authorized('invite', Project::class)],
            'email' => ['required', 'email', 'max:250', new InvitationEmail(), new NotOwnEmail($request->user())],
            'role' => ['required', Rule::enum(Role::class)],
        ])->stopOnFirstFailure()->validate();

        $invitation = $request->user()->invitations()->create($validated);

        $invitation->sendNotification();
        $invitation->load('account');

        return new InvitationResource($invitation);
    }
}
