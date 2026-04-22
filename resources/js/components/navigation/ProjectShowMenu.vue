<template>
    <slot name="before" />

    <NavbarButton v-if="['editor', 'owner'].includes(project.my_role)" :to="{ name: 'projects.requirements.create' }" icon="plus-lg" tooltip="Add requirement" class="hidden sm:flex" />
    <NavbarButton v-if="['editor', 'owner'].includes(project.my_role)" :to="{ name: 'projects.organise' }" icon="organise" tooltip="Organise" class="hidden sm:flex" />
    <NavbarButton v-if="settings.mode !== 'solo'" :to="{ name: 'projects.people' }" icon="people" tooltip="People" class="hidden sm:flex" />
    <NavbarButton v-if="settings.mode !== 'solo'" :to="{ name: 'projects.feedback' }" icon="feedback" tooltip="Feedback" class="hidden sm:flex" />

    <DropdownMenu class="hidden sm:flex">
        <template #trigger="{ toggle }">
            <Tooltip text="Download">
                <button type="button" class="flex items-center whitespace-nowrap gap-2 p-2 rounded-full border border-gray-100 transition-colors" @click="toggle">
                    <IconSet name="download" class="size-6" />
                </button>
            </Tooltip>
        </template>

        <DropdownMenuItem :href="'/api/export/' + project.id + '/html'" icon="html-file" download>HTML</DropdownMenuItem>
        <DropdownMenuItem :href="'/api/export/' + project.id + '/markdown'" icon="markdown-file" download>Markdown</DropdownMenuItem>
        <DropdownMenuItem :href="'/api/export/' + project.id + '/json'" icon="json-file" download>JSON</DropdownMenuItem>
    </DropdownMenu>

    <slot name="after" />
</template>

<script>
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import IconSet from '@/components/IconSet.vue';
import NavbarButton from '@/components/NavbarButton.vue';
import Tooltip from '@/components/Tooltip.vue';

export default {
    components: {
        DropdownMenu,
        DropdownMenuItem,
        IconSet,
        NavbarButton,
        Tooltip,
    },
    inject: ['settings'],
    props: ['project'],
};
</script>
