<template>
    <DropdownMenu>
        <template #trigger="{ toggle }">
            <button type="button" class="flex gap-2 items-center p-2 -mr-2" @click="toggle">
                <IconSet name="person-circle" class="size-6 shrink-0" />

                <span v-if="!is_solo && account" class="hidden lg:block whitespace-nowrap">{{ account.name }}</span>
            </button>
        </template>

        <DropdownMenuItem href="/docs" target="_blank" icon="documentation">Documentation</DropdownMenuItem>
        <DropdownMenuItem v-if="!is_solo" :to="{ name: 'account.settings' }" icon="settings">Settings</DropdownMenuItem>
        <DropdownMenuItem v-if="!is_solo" @click="logout()" icon="logout">Log out</DropdownMenuItem>
    </DropdownMenu>
</template>

<script>
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import IconSet from '@/components/IconSet.vue';
import Model from '@/stores/Model.js';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';

export default {
    components: {
        DropdownMenu,
        DropdownMenuItem,
        IconSet,
    },
    computed: {
        account: () => useAuthStore().account,
        is_solo() {
            return this.settings.mode === 'solo';
        },
    },
    inject: [
        'api',
        'settings',
    ],
    methods: {
        logout() {
            this.api.post('auth/logout')
                .finally(() => {
                    useAuthStore().is_logged_in = false;

                    this.$router.push({ name: 'auth.login' })
                        .then(() => {
                            useAuthStore().account = null;
                            
                            Model.resetRepositories();

                            useAlertsStore().push('You have been logged out.');
                        });
                });
        },
    },
};
</script>
