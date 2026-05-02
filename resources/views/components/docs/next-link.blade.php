@props(['href'])

<nav class="mt-12 flex justify-end border-t border-gray-100 pt-8">
    <a href="{{ $href }}" class="flex gap-2 items-center text-gray-500 hover:text-black transition-all duration-500">
        <span>{{ $slot }}</span>

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="fill-current size-6">
            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8"/>
        </svg>
    </a>
</nav>
