<?php

namespace Spectacular\Core\Actions;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Unknown;

class UnknownDelete
{
    use AsAction;

    public function handle(Unknown $unknown): void
    {
        $unknown->delete();
    }

    public function asController(Unknown $unknown): Response
    {
        $this->handle($unknown);

        return response()->noContent();
    }
}
