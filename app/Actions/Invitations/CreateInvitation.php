<?php

namespace App\Actions\Invitations;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use App\Enums\Role;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\Project;
use App\Rules\Authorised;
use App\Rules\InvitationEmail;
use App\Rules\NotOwnEmail;
use Illuminate\Routing\Router;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateInvitation
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('invitations', static::class)
            ->middleware('sqids:project_id');
    }

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Invitation::class);
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorised('invite', Project::class)],
            'email' => ['required', 'email:filter', 'max:250', new InvitationEmail(), new NotOwnEmail($request->user())],
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->stopOnFirstFailure();
    }

    public function handle(array $validated, $account): Invitation
    {
        $invitation = $account->invitations()->create($validated);

        $invitation->sendNotification();
        $invitation->load('account');

        return $invitation;
    }

    public function asController(ActionRequest $request): InvitationResource
    {
        $invitation = $this->handle($request->validated(), $request->user());

        return new InvitationResource($invitation);
    }
}
