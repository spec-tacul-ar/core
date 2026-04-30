<x-layout>
    <x-slot:head>
        @vite(['resources/css/main.css'])
    </x-slot:head>

    {{ $slot }}
</x-layout>
