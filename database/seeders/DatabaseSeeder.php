<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use ErrorException;
use Spectacular\Core\Models\Project;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $json = file_get_contents(storage_path('example_project.json'));
        } catch (ErrorException $exception) {
            return;
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $project = Project::where('uuid', $data['uuid'])->first();

        if ($project && $project->created_at->notEqualTo($project->updated_at)) {
            return;
        }

        Project::import($data);
    }
}
