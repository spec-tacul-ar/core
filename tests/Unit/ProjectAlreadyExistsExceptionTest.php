<?php

namespace Tests\Unit;

use App\Exceptions\ProjectAlreadyExistsException;
use App\Models\Project;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function test_it_is_not_reported(): void
    {
        $exception = new ProjectAlreadyExistsException(new Project([
            'name' => 'Existing Project',
        ]));

        $this->assertInstanceOf(ShouldntReport::class, $exception);
    }

    public function test_it_renders_as_a_basic_json_message_for_json_requests(): void
    {
        $project = new Project([
            'name' => 'Existing Project',
        ]);

        $exception = new ProjectAlreadyExistsException($project);
        $request = Request::create('/api/projects/demo', 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $exception->render($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('A project with this UUID already exists.', $response->getData(true)['message']);
    }
}
