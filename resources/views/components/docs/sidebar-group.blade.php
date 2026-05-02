@props(['title' => null])

<section>
    @if ($title)
        <h2 class="font-display mb-2 text-xs font-semibold uppercase text-gray-400">{{ $title }}</h2>
    @endif

    <div class="space-y-1">
        {{ $slot }}
    </div>
</section>
