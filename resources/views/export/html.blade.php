<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->name }}</title>
    <style>{!! Vite::content('resources/css/export.css') !!}</style>
</head>
<body>
    <main>
        <h1>{{ $project->name }}</h1>

        @if ($project->description)
            <div id="introduction">
                {!! clean($project->description) !!}
            </div>
        @endif

        @if ($project->actors->isNotEmpty())
            <div id="users">
                <h2>Users</h2>

                @foreach ($project->actors as $actor)
                    <div class="user">
                        <h3>{{ $actor->name }}</h3>
                        <p>{!! nl2br(e($actor->summary)) !!}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($project->features->isNotEmpty())
            <div id="features">
                <h2>Features</h2>

                @foreach ($project->features as $feature)
                    <div class="feature">
                        <h3>{{ $feature->name }}</h3>
                        {!! clean($feature->description) !!}

                        @if ($feature->requirements->isNotEmpty())
                            <div class="requirements">
                                @foreach ($feature->requirements as $requirement)
                                    <div class="requirement">
                                        <h4>
                                            {{ $requirement->title }}
                                            @if ($requirement->is_blocked)
                                                <span class="status status-blocked">Blocked</span>
                                            @endif
                                            @if (!$requirement->is_blocked && $requirement->is_complete)
                                                <span class="status status-complete">Complete</span>
                                            @endif
                                        </h4>

                                        @if ($requirement->is_blocked)
                                            <p class="blocker">{{ $requirement->blocked_reason }}</p>
                                        @endif

                                        @if ($requirement->description)
                                            <div class="description">
                                                {!! clean($requirement->description) !!}
                                            </div>
                                        @endif

                                        @if ($requirement->source)
                                            <p class="source">{{ $requirement->source }}</p>
                                        @endif

                                        @if ($requirement->unknowns->isNotEmpty())
                                            <div class="unknowns">
                                                <h5>Unknowns</h5>

                                                <ul>
                                                    @foreach ($requirement->unknowns as $unknown)
                                                        <li>{{ $unknown->name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if ($requirement->tasks->isNotEmpty())
                                            <div class="tasks">
                                                <h5>Tasks</h5>

                                                <ul>
                                                    @foreach ($requirement->tasks as $task)
                                                        <li>
                                                            {{ $task->name }}
                                                            @if ($task->estimate)
                                                                <span class="estimate">{{ $task->estimate }}</span>
                                                            @endif
                                                            @if ($task->is_complete)
                                                                <span class="status status-complete">Complete</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($project->total_estimate > 0)
            <h2>Summary</h2>

            <table>
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Estimate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project->features as $feature)
                        <tr>
                            <td>{{ $feature->name }}</td>
                            <td>{{ $feature->requirements_estimate }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <td>{{ $project->total_estimate }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </main>
</body>
</html>
