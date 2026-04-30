@props(['href'])

@php
    $active = request()->is(trim($href, '/') ?: '/');
@endphp

<a
    href="{{ $href }}"
    @class([
        'block rounded-lg px-3 py-2 text-sm transition',
        'bg-gray-100 font-semibold text-gray-950' => $active,
        'text-gray-500 hover:bg-gray-50 hover:text-gray-950' => ! $active,
    ])>
    {{ $slot }}
</a>

@isset($anchors)
    @if ($active)
        <div class="mt-1 space-y-1 py-1 pl-3">
            {{ $anchors }}
        </div>
    @endif
@endisset
