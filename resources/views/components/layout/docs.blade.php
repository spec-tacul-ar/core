@props(['title' => 'Documentation'])

<x-layout :title="$title . ' - ' . config('app.name')">
    <x-slot:head>
        @vite(['resources/js/docs.js'])
    </x-slot:head>

    <div
        x-data="{ is_open: false, is_mobile: false }"
        x-resize.document="is_mobile = window.getComputedStyle($refs.burger).display !== 'none'"
        class="min-h-screen bg-white text-gray-800">

        <header class="fixed inset-x-0 top-0 z-20 flex h-16 items-center border-b border-gray-100 bg-white">
            <a href="/" class="flex h-full shrink-0 items-center justify-center px-4 xl:w-(--sidebar-width) xl:border-r xl:border-gray-100">
                <img src="/images/logo.svg" alt="Spectacular" class="h-8 w-auto mt-1">
            </a>

            <div class="flex w-full items-center justify-end gap-4 px-4">
                <a href="https://www.github.com/syntheticminds/spectacular">
                    <span class="sr-only">GitHub</span>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="fill-current size-6">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                    </svg>
                </a>

                <a href="/app" class="btn btn-primary hidden xl:flex">Open App</a>

                <button x-ref="burger" type="button" class="-mr-2 flex size-10 items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 xl:hidden" @click="is_open = !is_open">
                    <span class="relative block size-5">
                        <span class="absolute left-0 top-1/2 h-0.5 w-full rounded bg-current transition" :class="is_open ? 'rotate-45' : '-translate-y-2'"></span>
                        <span class="absolute left-0 top-1/2 h-0.5 w-full rounded bg-current transition" :class="is_open ? 'opacity-0' : 'opacity-100'"></span>
                        <span class="absolute left-0 top-1/2 h-0.5 w-full rounded bg-current transition" :class="is_open ? '-rotate-45' : 'translate-y-2'"></span>
                    </span>
                </button>
            </div>
        </header>

        <div class="flex">
            <div
                @click="is_open = false"
                class="transition-color duration-250"
                :class="[
                    is_mobile ? 'fixed inset-0' : 'hidden',
                    is_open ? 'bg-black/20 pointer-events-auto' : 'bg-black/0 pointer-events-none',
                ]">

                {{-- Backdrop --}}
            </div>

            <aside
                class="fixed bottom-0 left-0 top-16 z-30 w-(--sidebar-width) max-w-full shrink-0 xl:translate-x-0"
                :class="[
                    is_open ? 'translate-x-0' : '-translate-x-full',
                    is_mobile ? 'transition-transform' : 'transition-none',
                ]"
                x-trap.noscroll="is_open && is_mobile"
                x-cloak>

                <div class="h-full overflow-y-auto border-r border-gray-100 bg-white p-8">
                    <a href="/app" class="btn btn-primary mb-8 xl:hidden">Open App</a>

                    <nav class="space-y-8">
                        <x-docs.sidebar-group>
                            <x-docs.sidebar-link label="Introduction" href="/docs">
                                <x-docs.sidebar-sublink href="/docs#who-is-spectacular-for">Who is Spectacular for?</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs#methodology">Methodology</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs#ai-assisted-development">AI-assisted development</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link label="Get Started" href="/docs/get-started">
                                <x-docs.sidebar-sublink href="/docs/get-started#cloud">Cloud</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/get-started#self-hosted">Self-hosted</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>
                        </x-docs.sidebar-group>

                        <x-docs.sidebar-group label="Core Concepts">
                            <x-docs.sidebar-link label="Projects" href="/docs/projects">
                                <x-docs.sidebar-sublink href="/docs/projects#organising">Organising</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/projects#exporting">Exporting</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/projects#filtering">Filtering</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link label="Users" href="/docs/users"></x-docs.sidebar-link>

                            <x-docs.sidebar-link label="Features" href="/docs/features"></x-docs.sidebar-link>

                            <x-docs.sidebar-link label="Requirements" href="/docs/requirements">
                                <x-docs.sidebar-sublink href="/docs/requirements#tasks">Tasks</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/requirements#unknowns">Unknowns</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>
                        </x-docs.sidebar-group>

                        <x-docs.sidebar-group label="Collaborating">
                            <x-docs.sidebar-link label="Team" href="/docs/team">
                                <x-docs.sidebar-sublink href="/docs/team#roles">Roles</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/team#invitations">Invitations</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link label="Feedback" href="/docs/feedback"></x-docs.sidebar-link>
                        </x-docs.sidebar-group>
                    </nav>
                </div>
            </aside>

            <main class="mx-auto mt-16 w-full max-w-4xl px-4 py-8 xl:ml-(--sidebar-width) xl:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layout>
