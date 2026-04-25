<?php

namespace Tests\Unit;

use App\Exceptions\ProjectAlreadyExistsException;
use App\Models\Project;
use Tests\TestCase;

class ProjectAlreadyExistsExceptionTest extends TestCase
{
    public function test_it_exposes_the_conflicting_project_and_default_message(): void
    {
        $project = new Project([
            'name' => 'Existing Project',
        ]);

        $exception = new ProjectAlreadyExistsException($project);

        $this->assertSame($project, $exception->project);
        $this->assertSame('A project with this UUID already exists.', $exception->getMessage());
    }
}
