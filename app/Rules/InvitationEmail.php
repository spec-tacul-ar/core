<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Project;

class InvitationEmail implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $project_id = $this->data['project_id'] ?? null;

        if (blank($project_id)) {
            return;
        }

        $project = Project::find($project_id);

        if (!$project) {
            return;
        }

        if ($project->invitations()->where('email', $value)->exists()) {
            $fail('An invitation with that email address already exists.');
        }

        // Note: this could leak email address attachment if the project_id is not checked first with stopOnFirstFailure() enabled.
        if ($project->accounts()->where('email', $value)->exists()) {
            $fail('A user with that email address is already attached to this project.');
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
