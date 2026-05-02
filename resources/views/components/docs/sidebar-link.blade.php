@props(['title', 'path'])

<a href="{{ $path }}" @class([
    'block rounded-lg px-3 py-2 text-sm transition',
    request()->is($path) ? 'bg-gray-100 font-semibold text-gray-950' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-950'
])>{{ $title }}</a>

@if ($slot->isNotEmpty() && request()->is($path))
    <div class="mt-1 space-y-1 py-1 pl-3">
        {{ $slot }}
    </div>
@endif
