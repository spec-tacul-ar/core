<template>
    <div class="flex items-center gap-2 py-4">
        <p class="font-semibold mr-auto">{{ contributor.account_name }}</p>

        <p v-if="!can_edit">{{ contributor.role_name }}</p>

        <DropdownMenu v-else ref="dropdown">
            <template #trigger="{ toggle }">
                <button type="button" class="flex items-end gap-1 border rounded-full bg-gray-200 leading-none px-2 py-1" @click="toggle">
                    {{ contributor.role_name }}

                    <IconSet name="caret-down" class="size-3" />
                </button>
            </template>

            <DropdownMenuItem
                @click="change('owner')"
                :icon="waiting_for === null && contributor.role === 'owner' ? 'check-lg' : 'none'"
                :disabled="waiting_for !== null || contributor.role === 'owner'"
                :loading="waiting_for === 'owner'">

                Owner
            </DropdownMenuItem>
            
            <DropdownMenuItem
                @click="change('editor')"
                :icon="waiting_for === null && contributor.role === 'editor' ? 'check-lg' : 'none'"
                :disabled="waiting_for !== null || contributor.role === 'editor'"
                :loading="waiting_for === 'editor'">

                Editor
            </DropdownMenuItem>
            
            <DropdownMenuItem
                @click="change('viewer')"
                :icon="waiting_for === null && contributor.role === 'viewer' ? 'check-lg' : 'none'"
                :disabled="waiting_for !== null || contributor.role === 'viewer'"
                :loading="waiting_for === 'viewer'">

                Viewer
            </DropdownMenuItem>

            <div class="h-px w-full bg-gray-200 my-2"></div>

            <DropdownMenuItem
                @click="remove"
                icon="x-lg"
                :disabled="waiting_for !== null"
                :loading="waiting_for === 'remove'"
                danger>

                Remove
            </DropdownMenuItem>
        </DropdownMenu>
    </div>
</template>

<script>
import Contributor from '@/stores/models/Contributor';
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import IconSet from '@/components/IconSet.vue';
import Model from '@/stores/Model.js';
import { isBefore, isEqual } from "date-fns";
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';
        
export default {
    components: {
        DropdownMenu,
        DropdownMenuItem,
        IconSet,
    },
    computed: {
        can_edit() {
            if (!this.contributor.project.can_manage) {
                return false;
            }

            if (this.contributor.role === 'owner') {
                if (this.project_has_one_owner) {
                    return false;
                }

                if (isBefore(this.contributor.updated_at, this.me.updated_at)) {
                    return false;
                }

                if (isEqual(this.contributor.updated_at, this.me.updated_at) && !this.is_me) {
                    return false;
                }
            }

            return true;
        },
        me() {
            return this.contributor.project.contributors.firstWhere('account_id', useAuthStore().account.id);
        },
        is_me() {
            return this.me.is(this.contributor);
        },
        project_has_one_owner() {
            return this.contributor.project.contributors.where('role', 'owner').count() === 1;
        },
    },
    data() {
        return {
            waiting_for: null,
        };
    },
    inject: [
        'api',
    ],
    methods: {
        change(role) {
            if (!this.project_has_one_owner && this.is_me) {
                if (!confirm('Are you sure you wish to give up ownership of this project?')) {
                    this.$refs.dropdown.close();

                    return;
                }
            }

            this.waiting_for = role;

            this.api.post('contributors/' + this.contributor.id + '/edit', {role})
                .then((result) => {
                    const contributor = result.data;
                    
                    Contributor.repository().save(contributor);

                    this.$refs.dropdown.close();

                    useAlertsStore().push('Role changed.');
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.waiting_for = null);
        },
        remove() {
            if (!this.project_has_one_owner && this.is_me) {
                if (!confirm('Are you sure you wish to leave this project entirely?')) {
                    this.$refs.dropdown.close();

                    return;
                }
            }

            const was_me = this.is_me;

            this.waiting_for = 'remove';

            this.api.post('contributors/' + this.contributor.id + '/delete')
                .then(() => {
                    useAlertsStore().push('Contributor removed.');
                    
                    Contributor.repository().delete(this.contributor.id);

                    this.$refs.dropdown.close();

                    if (was_me) {
                        Model.resetRepositories();

                        this.$router.push({ name: 'projects.index' });
                    }
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.waiting_for = null);
        },
    },
    props: [
        'contributor'
    ],
};
</script>
