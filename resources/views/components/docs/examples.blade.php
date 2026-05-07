<div class="flex gap-4 mb-4">
    @if (isset($good) && $good->isNotEmpty())
        <ul class="group good flex-1 m-0 bg-gray-50 p-2">{{ $good }}</ul>
    @endif

    @if (isset($bad) && $bad->isNotEmpty())
        <ul class="group bad flex-1 m-0 bg-gray-50 p-2">{{ $bad }}</ul>
    @endif
</div>
