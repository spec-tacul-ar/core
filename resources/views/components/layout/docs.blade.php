@props(['title' => 'Documentation'])

<x-layout :title="$title . ' - ' . config('app.name')">
    <x-slot:head>
        @vite(['resources/css/main.css'])
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </x-slot:head>

    <div
        x-data="{
            sidebarOpen: false,
            sidebarTransition: false,
            media: null,
            isMobile: window.matchMedia('(max-width: 767px)').matches,
            closeSidebar() {
                this.sidebarTransition = true;
                this.sidebarOpen = false;
                setTimeout(() => this.sidebarTransition = false, 200);
            },
            toggleSidebar() {
                this.sidebarTransition = true;
                this.sidebarOpen = ! this.sidebarOpen;
                setTimeout(() => this.sidebarTransition = false, 200);
            },
        }"
        x-init="
            media = window.matchMedia('(max-width: 767px)');
            const updateMedia = () => {
                isMobile = media.matches;

                if (! isMobile) {
                    sidebarOpen = false;
                }
            };
            media.addEventListener('change', updateMedia);
            updateMedia();
        "
        @keydown.escape.window="closeSidebar()"
        class="min-h-screen bg-white text-gray-800">
        <header class="fixed inset-x-0 top-0 z-20 flex h-16 items-center border-b border-gray-100 bg-white">
            <a href="/" class="flex h-full shrink-0 items-center justify-center px-4 md:w-(--sidebar-width) md:border-r md:border-gray-100">
                <img src="/images/logo.svg" alt="{{ config('app.name') }}" class="h-8 w-auto mt-1">
            </a>

            <div class="flex w-full items-center justify-end gap-2 px-4">
                <a href="/app" class="font-display hidden items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black md:flex">
                    Open App
                </a>

                <button
                    type="button"
                    aria-label="Toggle documentation menu"
                    :aria-expanded="sidebarOpen.toString()"
                    class="-mr-2 flex size-10 items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 md:hidden"
                    @click="toggleSidebar()">

                    <span class="sr-only" x-text="sidebarOpen ? 'Close documentation menu' : 'Open documentation menu'"></span>
                    <span class="relative block size-5">
                        <span
                            class="absolute left-0 top-1/2 h-0.5 w-full rounded bg-current transition"
                            :class="sidebarOpen ? 'rotate-45' : '-translate-y-2'"></span>
                        <span
                            class="absolute left-0 top-1/2 h-0.5 w-full rounded bg-current transition"
                            :class="sidebarOpen ? 'opacity-0' : 'opacity-100'"></span>
                        <span
                            class="absolute left-0 top-1/2 h-0.5 w-full rounded bg-current transition"
                            :class="sidebarOpen ? '-rotate-45' : 'translate-y-2'"></span>
                    </span>
                </button>
            </div>
        </header>

        <div class="flex">
            <button
                type="button"
                aria-label="Close documentation menu"
                x-cloak
                x-show="sidebarOpen && isMobile"
                x-transition.opacity
                class="fixed inset-x-0 bottom-0 top-16 z-20 bg-gray-950/20 md:hidden"
                @click="closeSidebar()">
            </button>

            <aside
                x-cloak
                x-trap.noscroll="sidebarOpen && isMobile"
                class="fixed bottom-0 left-0 top-16 z-30 w-80 max-w-[calc(100vw-2rem)] shrink-0 md:translate-x-0"
                :class="[
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                    sidebarTransition ? 'transition-transform duration-200' : '',
                ]">

                <div class="h-full overflow-y-auto border-r border-gray-100 bg-white px-6 py-8">
                    <a href="/app" class="font-display mb-8 flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black md:hidden">
                        Open App
                    </a>

                    <nav class="space-y-8">
                        <x-docs.sidebar-section>
                            <x-docs.sidebar-link href="/docs">
                                Introduction

                                <x-slot:anchors>
                                    <a href="#what-spectacular-is" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">What Spectacular is</a>
                                    <a href="#methodology" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Methodology</a>
                                    <a href="#core-model" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">The core model</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link href="/docs/get-started">
                                Get started

                                <x-slot:anchors>
                                    <a href="#cloud" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Cloud</a>
                                    <a href="#self-hosting" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Self-hosting</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>
                        </x-docs.sidebar-section>

                        <x-docs.sidebar-section title="Building Specs">
                            <x-docs.sidebar-link href="/docs/projects">
                                Projects

                                <x-slot:anchors>
                                    <a href="#creating-projects" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Creating projects</a>
                                    <a href="#organising" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Organising</a>
                                    <a href="#exporting" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Exporting</a>
                                    <a href="#deleting" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Deleting</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link href="/docs/users-and-features">
                                Users &amp; features

                                <x-slot:anchors>
                                    <a href="#users" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Users</a>
                                    <a href="#features" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Features</a>
                                    <a href="#using-them-together" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Using them together</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link href="/docs/requirements">
                                Requirements

                                <x-slot:anchors>
                                    <a href="#writing-requirements" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Writing requirements</a>
                                    <a href="#tasks" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Tasks</a>
                                    <a href="#unknowns" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Unknowns</a>
                                    <a href="#filtering" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Filtering</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>
                        </x-docs.sidebar-section>

                        <x-docs.sidebar-section title="Collaboration">
                            <x-docs.sidebar-link href="/docs/roles">
                                Roles

                                <x-slot:anchors>
                                    <a href="#owner" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Owner</a>
                                    <a href="#editor" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Editor</a>
                                    <a href="#viewer" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Viewer</a>
                                    <a href="#leaving-a-project" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Leaving a project</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>

                            <x-docs.sidebar-link href="/docs/invitations">
                                Invitations

                                <x-slot:anchors>
                                    <a href="#sending-invitations" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Sending invitations</a>
                                    <a href="#accepting-invitations" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Accepting invitations</a>
                                    <a href="#managing-invitations" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Managing invitations</a>
                                    <a href="#choosing-the-right-role" class="block px-3 py-1 text-sm text-gray-500 transition hover:text-gray-950">Choosing the right role</a>
                                </x-slot:anchors>
                            </x-docs.sidebar-link>
                        </x-docs.sidebar-section>
                    </nav>
                </div>
            </aside>

            <main class="mx-auto mt-16 w-full max-w-4xl px-4 py-10 md:ml-80 md:px-10 lg:ml-80">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layout>
