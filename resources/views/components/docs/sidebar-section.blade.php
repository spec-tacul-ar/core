@props(['title' => null])

<section>
    @if ($title)
        <h2 class="font-display mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $title }}</h2>
    @endif

    <div class="space-y-1">
        {{ $slot }}
    </div>
</section>
