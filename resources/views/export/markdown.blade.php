# {{ Str::escapeMarkdown($project->name) }}

@if ($project->description)
{{----}}{{ Str::htmlToMarkdown($project->description) }}
@endif

@if ($project->actors->isNotEmpty())
{{----}}## Users

{{----}}@foreach ($project->actors as $actor)
{{----}}{{----}}### {{ Str::escapeMarkdown($actor->name) }}
{{----}}{{----}}{{ $actor->summary ? Str::escapeMarkdown($actor->summary) . "\n" : '' }}
{{----}}@endforeach
@endif
@if ($project->features->isNotEmpty())
{{----}}## Features

{{----}}@foreach ($project->features as $feature)
{{----}}{{----}}### {{ Str::escapeMarkdown($feature->name) }}
{{----}}{{----}}
{{----}}{{----}}{{ $feature->description ? Str::htmlToMarkdown($feature->description) . "\n" : '' }}
{{----}}{{----}}@foreach ($feature->requirements as $requirement)
{{----}}{{----}}{{----}}#### {{ Str::escapeMarkdown($requirement->title) }}{{ $requirement->is_complete && !$requirement->is_blocked ? ' [Complete]' : null }}
{{----}}{{----}}{{----}}{{ $requirement->is_blocked ? "\n" . '**[Blocked] ' . Str::escapeMarkdown($requirement->blocked_reason) . '**' . "\n" : '' }}
{{----}}{{----}}{{----}}{{ $requirement->description ? Str::htmlToMarkdown($requirement->description) . "\n" : '' }}
{{----}}{{----}}{{----}}{{ $requirement->source ? '*Source: ' . Str::escapeMarkdown($requirement->source) . '*' . "\n" : '' }}
{{----}}{{----}}{{----}}@if ($requirement->tasks->isNotEmpty())
{{----}}{{----}}{{----}}{{----}}##### Tasks
{{----}}{{----}}{{----}}{{----}}@foreach ($requirement->tasks as $task)
{{----}}{{----}}{{----}}{{----}}{{----}}* {{ Str::escapeMarkdown($task->name) }}{{ $task->estimate !== null ? ' [Estimate: ' . $task->estimate . 'h]' : null }}{{ $task->is_complete ? ' [Complete]' : null }}
{{----}}{{----}}{{----}}{{----}}@endforeach
{{----}}{{----}}{{----}}{{----}}
{{----}}{{----}}{{----}}@endif
{{----}}{{----}}{{----}}@if ($requirement->unknowns->isNotEmpty())
{{----}}{{----}}{{----}}{{----}}##### Unknowns
{{----}}{{----}}{{----}}{{----}}@foreach ($requirement->unknowns as $unknown)
{{----}}{{----}}{{----}}{{----}}{{----}}* {{ Str::escapeMarkdown($unknown->name) }}
{{----}}{{----}}{{----}}{{----}}@endforeach
{{----}}{{----}}{{----}}{{----}}
{{----}}{{----}}{{----}}@endif
{{----}}{{----}}@endforeach
{{----}}@endforeach
@endif
@if ($project->total_estimate > 0)
{{----}}## Summary
{{----}}| Feature | Estimate |
{{----}}| :--- | ---: |
{{----}}@foreach ($project->features as $feature)
{{----}}{{----}}| {{ Str::escapeMarkdown($feature->name) }} | {{ $feature->requirements_estimate }}h |
{{----}}@endforeach
{{----}}| **Total** | {{ $project->total_estimate }}h |
@endif
