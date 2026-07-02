@props(['title' => 'Documentation'])

<x-layout :title="$title . ' - ' . config('app.name')">
    <x-slot:head>
        @vite(['resources/css/main.css', 'resources/js/main.js'])
    </x-slot:head>

    <div
        x-data="{ is_open: false, is_mobile: false }"
        x-resize.document="is_mobile = window.getComputedStyle($refs.burger).display !== 'none'"
        class="min-h-screen bg-white text-gray-800 dark:bg-gray-950 dark:text-gray-400">

        <header class="fixed inset-x-0 top-0 z-20 flex h-16 items-center border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-950">
            <a href="/" class="flex h-full shrink-0 items-center justify-center px-4 xl:w-(--sidebar-width) xl:border-r xl:border-gray-100 dark:xl:border-gray-800">
                <img src="/images/logo.svg" alt="Spectacular" class="h-8 w-auto mt-1 dark:brightness-0 dark:invert">
            </a>

            <div class="flex w-full items-center justify-end gap-4 px-4">
                <a href="https://x.com/spec_tacul_ar" class="text-gray-700 transition hover:text-gray-950 dark:text-gray-300 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="fill-current size-6">
                        <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
                    </svg>
                </a>

                <a href="https://www.youtube.com/@buildwithspectacular" class="text-gray-700 transition hover:text-gray-950 dark:text-gray-300 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="fill-current size-6">
                        <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z"/>
                    </svg>
                </a>
                
                <a href="https://www.github.com/syntheticminds/spectacular" class="text-gray-700 transition hover:text-gray-950 dark:text-gray-300 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="fill-current size-6">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                    </svg>
                </a>

                <a href="/app" class="btn btn-primary hidden xl:flex">Open App</a>

                <button x-ref="burger" type="button" class="-mr-2 flex size-10 items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 xl:hidden" @click="is_open = !is_open">
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
                    is_open ? 'bg-black/20 dark:bg-black/50 pointer-events-auto' : 'bg-black/0 pointer-events-none',
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

                <div class="h-full overflow-y-auto border-r border-gray-100 bg-white p-8 dark:border-gray-800 dark:bg-gray-950">
                    <a href="/app" class="btn btn-primary mb-8 xl:hidden">Open App</a>

                    <nav class="space-y-8">
                        <x-docs.sidebar-group>
                            <x-docs.sidebar-link label="Introduction" href="/docs">
                                <x-docs.sidebar-sublink href="/docs#methodology">Methodology</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs#agent-context">Context For AI Agents</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link label="Get Started" href="/docs/get-started">
                                <x-docs.sidebar-sublink href="/docs/get-started#your-first-project">Your First Project</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/get-started#self-hosting">Self-Hosting</x-docs.sidebar-sublink>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link label="AI-assisted Development" href="/docs/ai">
                                <x-docs.sidebar-sublink href="/docs/ai#workflow">Workflow</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/ai#prototyping">Rapid Prototyping</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/ai#connect-agents">Connecting MCP</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/ai#authentication">Authentication</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/ai#available-tools">Available Tools</x-docs.sidebar-sublink>
                                <x-docs.sidebar-sublink href="/docs/ai#example-prompts">Example Prompts</x-docs.sidebar-sublink>
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
