<?php

namespace App\Exceptions;

use App\Models\Project;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ProjectAlreadyExistsException extends RuntimeException implements ShouldntReport
{
    public function __construct(public Project $project)
    {
        parent::__construct('A project with this UUID already exists.');
    }

    public function render(Request $request): Response
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 409);
    }
}
