@props(['label' => null])

<section>
    @if ($label)
        <h2 class="font-display mb-2 text-xs font-semibold uppercase text-gray-400">{{ $label }}</h2>
    @endif

    <div class="space-y-1">
        {{ $slot }}
    </div>
</section>
