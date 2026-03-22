<template>
    <div class="flex items-center flex-wrap gap-4 p-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-lg font-semibold">{{ invitation.project_name }}</span>
                <span class="inline-flex gap-2 leading-none p-1 px-2 text-sm rounded-full items-center bg-gray-200 text-gray-500">
                    {{ invitation.role_name }}
                </span>
            </div>
            <p class="text-sm">Invited by {{ invitation.account_name }} {{ created_at }} ago</p>
        </div>
        <div class="ml-auto">
            <div class="flex justify-end gap-1 ml-auto">
                <button @click="accept" type="button" class="btn btn-sm btn-primary">Accept</button>
                <button @click="decline" type="button" class="btn btn-sm btn-danger">Decline</button>
            </div>
        </div> 
    </div>
</template>

<script>
import Invitation from '@/stores/models/Invitation';
import { formatDistance } from 'date-fns';
import { useAlertsStore } from '@/stores';

export default {
    computed: {
        created_at() {
            return formatDistance(this.invitation.created_at, new Date());
        },
    },
    data() {
        return {
            is_accepting: false,
            is_deleting: false,
        };
    },
    inject: [
        'api',
    ],
    methods: {
        accept() {
            this.is_accepting = true;

            this.api.post('invitations/' + this.invitation.id + '/accept')
                .then(() => {
                    useAlertsStore().push('Invitation accepted.');
                    
                    Invitation.repository().delete(this.invitation.id);

                    this.$router.push({ name: 'projects.show', params: { project_id: this.invitation.project_id }});
                })
                .finally(() => this.is_accepting = false);
        },
        decline() {
            this.is_deleting = true;

            this.api.post('invitations/' + this.invitation.id + '/delete')
                .then(() => {
                    useAlertsStore().push('Invitation declined.');
                    
                    Invitation.repository().delete(this.invitation.id);
                })
                .finally(() => this.is_deleting = false);
        },
    },
    props: [
        'invitation'
    ],
};
</script>
