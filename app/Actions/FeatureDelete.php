<?php

namespace Spectacular\Core\Actions;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Feature;

class FeatureDelete
{
    use AsAction;

    public function handle(Feature $feature): void
    {
        $feature->delete();
    }

    public function asController(Feature $feature): Response
    {
        $this->handle($feature);

        return response()->noContent();
    }
}
