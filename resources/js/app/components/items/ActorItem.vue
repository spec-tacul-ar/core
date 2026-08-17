<template>
    <div class="flex justify-between items-start">
        <div>
            <h3 class="font-semibold text-xl mb-2">{{ actor.name }}</h3>
            <TextMultiline v-if="actor.summary" :text="actor.summary" />
            <p v-if="!actor.summary" class="text-gray-400 dark:text-gray-500">{{ $t('No description', project.locale) }}</p>
        </div>

        <DropdownMenu v-if="project.can_write" class="d-print-none">
            <DropdownMenuItem icon="edit" :to="{name: 'projects.actors.edit', params: { actor_id: actor.id }}" replace>Edit</DropdownMenuItem>
            <DropdownMenuItem icon="trash" @click="openActorDeleteModal" danger replace>Delete</DropdownMenuItem>
        </DropdownMenu>
    </div>
</template>

<script>
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import TextMultiline from '@/components/TextMultiline.vue';
import ActorDelete from '@/components/modals/ActorDelete.vue';
import { useModalStore } from '@/stores';

export default {
    components: {
        DropdownMenu,
        DropdownMenuItem,
        TextMultiline
    },
    inject: ['project'],
    methods: {
        openActorDeleteModal() {
            useModalStore().open(ActorDelete, {actor: this.actor});
        },
    },
    props: [
        'actor'
    ],
};
</script>
