<template>
    <nav
        class="fixed z-10 flex items-center bg-white border-b border-gray-100 top-0 left-0 right-0 h-16 print:hidden"
        :class="[
            use_animations ? 'motion-reduce:transition-none transition-position duration-500' : '',
            show_sidepanel ? 'md:right-(--sidepanel-width)' : '',
        ]">

        <a
            v-if="is_projects_index"
            href="/"
            class="shrink-0 h-full flex justify-center items-center px-4 border-gray-100 md:w-(--sidebar-width) md:border-r">

            <img src="/images/logo.svg" class="h-8 w-auto mt-1" />
        </a>

        <RouterLink
            v-else
            :to="{name: 'projects.index'}"
            class="shrink-0 h-full flex justify-center items-center px-4 border-gray-100 md:w-(--sidebar-width) md:border-r">

            <img src="/images/logo.svg" class="h-8 w-auto mt-1" />
        </RouterLink>

        <div v-if="has_toggles" class="flex w-full items-center gap-2 px-4">
            <slot name="toggles" v-bind="{ openSidebar, closeSidebar, toggleSidebar, show_sidebar }"></slot>
        </div>

        <div class="flex w-full items-center justify-end gap-2 px-4">
            <slot name="menu"></slot>
            
            <UserMenu />
        </div>
    </nav>

    <div class="flex">
        <aside
            class="shrink-0 motion-reduce:transition-none print:hidden"
            :class="[
                !was_resized && use_animations ? 'transition-width duration-500' : '',
                show_sidebar && allow_sidebar && !float_sidebar ? 'w-(--sidebar-width)' : 'w-0',
            ]">

            <div
                id="sidebar"
                ref="sidebar"
                class="z-10 bg-white shadow-lg backdrop-blur-lg fixed overflow-y-auto w-(--sidebar-width) max-w-full top-16 bottom-0 pb-safe overscroll-contain motion-reduce:transition-none border-r border-gray-100"
                :class="[
                    use_animations ? 'transition-position duration-500' : '',
                    show_sidebar && allow_sidebar ? 'left-0' : '-left-(--sidebar-width)',
                ]">

                <slot name="sidebar" v-bind="{ closeSidebar, navigate }"></slot>
            </div>
        </aside>

        <div
            class="motion-reduce:transition-none"
            :class="[
                show_sidepanel && float_sidepanel ? 'grow-0' : 'grow',
                !was_resized && use_animations ? 'transition-grow duration-500' : '',
            ]"></div>

        <main ref="main" class="w-full max-w-(--main-width) p-4 py-8 top-0 mt-16 print:max-w-full print:mt-0">
            <slot v-bind="{ toggleSidepanel }" />
        </main>

        <div
            class="motion-reduce:transition-none"
            :class="[
                show_sidebar && float_sidebar ? 'grow-0' : 'grow',
                !was_resized && use_animations ? 'transition-grow duration-500' : '',
            ]"></div>

        <aside
            class="shrink-0 z-10 motion-reduce:transition-none print:hidden"
            :class="[
                !was_resized && use_animations ? 'transition-width duration-500' : '',
                show_sidepanel && !float_sidepanel ? 'w-(--sidepanel-width)' : 'w-0',
            ]">

            <div
                id="sidepanel"
                ref="sidepanel"
                class="bg-white backdrop-blur-lg shadow-lg fixed w-(--sidepanel-width) max-w-full top-0 bottom-0 overscroll-contain motion-reduce:transition-none"
                :class="[
                    show_sidepanel ? 'right-0' : '-right-(--sidepanel-width)',
                    use_animations ? 'transition-position duration-500' : '',
                ]">

                <slot name="sidepanel" v-bind="{ closeSidepanel }"></slot>

                <RouterView name="sidepanel" v-slot="{ Component }">
                    <transition mode="out-in" :duration="Component ? 0 : 500">
                        <component :is="Component" @close="closeSidepanel" :key="route_key" />
                    </transition>
                </RouterView>
            </div>
        </aside>
    </div>
</template>

<script>
import HelpLink from '@/components/HelpLink.vue';
import UserMenu from '@/components/navigation/UserMenu.vue';
import { useModalStore } from '@/stores';

export default {
    components: {
        HelpLink,
        UserMenu,
    },
    computed: {
        allow_sidebar() {
            if (!this.has_sidebar) {
                return false;
            }

            if (!this.show_sidepanel) {
                return true;
            }

            const sidebar_width = this.$refs.sidebar.offsetWidth;
            const sidepanel_width = this.$refs.sidepanel.offsetWidth;

            return sidebar_width + sidepanel_width < this.viewport_width;
        },
        float_sidebar() {
            const content_max_width = parseInt(getComputedStyle(this.$refs.main).maxWidth);
            const sidebar_width = this.$refs.sidebar.offsetWidth;
            const sidepanel_width = this.$refs.sidepanel.offsetWidth;

            if (this.show_sidepanel) {
                return this.viewport_width - sidebar_width - sidepanel_width < content_max_width;
            }

            return this.viewport_width - sidebar_width < content_max_width;
        },
        float_sidepanel() {
            const content_max_width = parseInt(getComputedStyle(this.$refs.main).maxWidth);
            const sidepanel_width = this.$refs.sidepanel.offsetWidth;

            return this.viewport_width - sidepanel_width < content_max_width;
        },
        has_sidebar() {
            return !!this.$slots.sidebar;
        },
        has_sidepanel() {
            return !!this.$slots.sidepanel;
        },
        has_toggles() {
            return !!this.$slots.sidebar;
        },
        is_apple() {
            return /(Mac|iPhone|iPod|iPad)/i.test(navigator.platform);
        },
        is_projects_index() {
            return this.$route.name === 'projects.index';
        },
        has_sidepanel_route() {
            const components = this.$route.matched.at(-1).components;

            return Object.hasOwn(components, 'sidepanel');
        },
        is_sidepanel_full_width() {
            const sidepanel_width = this.$refs.sidepanel?.offsetWidth;

            return sidepanel_width === this.viewport_width;
        },
        route_key() {
            return this.$route.path + '?' + new URLSearchParams(this.$route.query).toString();
        },
    },
    data() {
        return {
            is_mounted: false,
            show_sidebar: false,
            show_sidepanel: false,
            use_animations: false,
            viewport_width: document.body.clientWidth,
            was_resized: false,
            was_sidebar_nudged: false,
        };
    },
    inheritAttrs: false,
    methods: {
        closeSidebar() {
            this.show_sidebar = false;
            this.was_resized = false;
            this.was_sidebar_nudged = false;
        },
        closeSidepanel() {
            if (this.$route.matched.length > 1) {
                const has_sidepanel = Object.hasOwn(this.$route.matched.at(-1).components, 'sidepanel');

                if (has_sidepanel) {
                    const parent = this.$route.matched.at(-2);
                    
                    this.$router.replace({ name: parent.name });

                    return;
                }
            }

            this.show_sidepanel = false;
            this.was_resized = false;

            if (this.was_sidebar_nudged) {
                this.openSidebar();
                this.was_sidebar_nudged = false;
            }
        },
        navigate() {
            if (this.float_sidebar) {
                this.closeSidebar();
            }
        },
        openSidebar() {
            this.show_sidebar = true;
            this.was_resized = false;
        },
        openSidepanel() {
            this.show_sidepanel = true;
            this.was_resized = false;

            if (this.float_sidebar) {
                if (this.show_sidebar) {
                    this.was_sidebar_nudged = true;
                }

                this.show_sidebar = false;
                this.was_resized = false;
            }
        },
        resize() {
            this.viewport_width = document.body.clientWidth;
            this.was_resized = true;
        },
        toggleSidebar() {
            this.show_sidebar ? this.closeSidebar() : this.openSidebar();
        },
        toggleSidepanel() {
            this.show_sidepanel ? this.closeSidepanel() : this.openSidepanel();
        },
    },
    mounted() {
        this.is_mounted = true;

        window.addEventListener('resize', this.resize);

        if (this.has_sidepanel_route) {
            this.openSidepanel();
        }

        if (!this.float_sidebar && this.expanded && !this.show_sidepanel) {
            this.openSidebar();
        }

        setTimeout(() => this.use_animations = true, 0);

        document.addEventListener('keydown', event => {
            if (this.show_sidepanel && event.keyCode === 27 && !useModalStore().is_open) {
                this.closeSidepanel();
            }
        });
    },
    props: {
        expanded: Boolean,
    },
    unmounted() {
        window.removeEventListener('resize', this.resize);

        document.body.style.overflow = null;
    },
    watch: {
        '$route': {
            handler() {
                this.$nextTick(() => {
                    if (this.has_sidepanel_route) {
                        this.openSidepanel();
                    } else {
                        this.closeSidepanel();
                    }
                });
            },
        },
        show_sidepanel(value) {
            if (value && this.is_sidepanel_full_width) {
                document.body.style.overflow = 'hidden';

                return;
            }

            document.body.style.overflow = null;
        }
    },
};
</script>
