<template>
    <DropdownMenu>
        <template #trigger="{ toggle }">
            <button type="button" class="flex gap-2 items-center p-2 -mr-2 dark:text-gray-400" @click="toggle">
                <IconSet name="person-circle" class="size-6 shrink-0" />

                <span v-if="account" class="hidden lg:block whitespace-nowrap">{{ account.name }}</span>
            </button>
        </template>

        <DropdownMenuItem href="/docs" target="_blank" icon="documentation">Documentation</DropdownMenuItem>
        <DropdownMenuItem :to="{ name: 'account.integrations' }" icon="integrations">Integrations</DropdownMenuItem>
        <DropdownMenuItem :to="{ name: 'account.settings' }" icon="settings">Settings</DropdownMenuItem>
        <DropdownMenuItem @click="logout()" icon="logout">Log out</DropdownMenuItem>
    </DropdownMenu>
</template>

<script>
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import IconSet from '@/components/IconSet.vue';
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
    },
    inject: [
        'api',
    ],
    methods: {
        logout() {
            this.api.client().post('auth/logout')
                .finally(() => {
                    useAuthStore().status = null;
                    useAuthStore().resume_session = false;

                    this.$router.push({ name: 'auth.login' })
                        .then(() => {
                            useAlertsStore().push('You have been logged out.');
                        });
                });
        },
    },
};
</script>
