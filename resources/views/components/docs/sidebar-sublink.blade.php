@props(['fragment'])

<a
    href="#{{ $fragment }}"
    class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">

    {{ $slot }}
</a>
