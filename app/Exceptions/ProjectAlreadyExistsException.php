<?php

namespace App\Exceptions;

use App\Models\Project;
use RuntimeException;

class ProjectAlreadyExistsException extends RuntimeException
{
    public function __construct(public Project $project)
    {
        parent::__construct('A project with this UUID already exists.');
    }
}
