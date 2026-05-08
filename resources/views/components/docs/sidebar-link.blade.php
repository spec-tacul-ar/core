@props(['label'])

<a {{ $attributes->class([
    'block rounded-lg px-3 py-2 text-sm transition',
    request()->is(ltrim($attributes->get('href'), '/')) ? 'bg-gray-100 font-semibold text-gray-950' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-950'
]) }}>{{ $label }}</a>

@if ($slot->isNotEmpty())
    <div class="mt-1 space-y-1 py-1 pl-3">
        {{ $slot }}
    </div>
@endif
