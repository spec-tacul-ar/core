<template>
    <article class="flex items-center gap-4">
        <div class="flex flex-col mr-auto">
            <h3 class="text-lg font-semibold">{{ token.name }}</h3>
            <p class="text-sm text-gray-500">Created {{ created_at }}</p>
        </div>

        <p class="text-gray-500 text-sm">Expires {{ expires_at }}</p>

        <SpinnerButton type="button" class="btn btn-danger-outline" @click="revoke">Revoke</SpinnerButton>
    </article>
</template>

<script>
import SpinnerButton from '@/components/SpinnerButton.vue';
import Token from '@/stores/models/Token';
import { format, formatDistance } from 'date-fns';
import { useAlertsStore } from '@/stores';

export default {
    components: {
        SpinnerButton,
    },
    computed: {
        created_at() {
            return format(Date.parse(this.token.created_at), 'PP');
        },
        expires_at() {
            return formatDistance(this.token.expires_at, new Date());
        },
    },
    data() {
        return {
            is_revoking: false,
        };
    },
    inject: ['api'],
    methods: {
        revoke() {
            if (confirm('Are you sure you want to revoke access?')) {
                this.is_revoking = true;

                this.api.post('tokens/' + this.token.id + '/revoke')
                    .then(() => {
                        Token.repository().delete(this.token.id);
                        useAlertsStore().push('Token revoked.');
                    })
                    .finally(() => this.is_revoking = false);
            }
        },
    },
    props: ['token'],
};
</script>
