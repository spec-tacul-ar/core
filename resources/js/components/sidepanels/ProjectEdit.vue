<template>
    <SidepanelLayout>
        <template #toolbar>
            <Tooltip text="Delete project">
                <button type="button" class="p-2" @click="openProjectDeleteModal()">
                    <IconSet name="trash" class="text-red-400 size-6" />
                </button>
            </Tooltip>
        </template>

        <template #buttons>
            <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting" @click="submit">Save</SpinnerButton>
        </template>
        
        <form @submit.prevent="submit">
            <h2 class="text-4xl font-light mb-4">Edit project</h2>

            <div class="mb-4">
                <FormInput type="text" :id="elementId('name')" label="Name" :error="errors.name" v-model="form.name" required> 
                    <template v-slot:help>
                        <p class="font-semibold mb-4">The name of the project. This might be the working title for now and can always be changed.</p>
                        <p>Examples:</p>
                        <ul class="list-disc italic ml-6">
                            <li>Website Upgrade</li>
                            <li>Booking System</li>
                            <li>App Prototype</li>
                        </ul>
                    </template>
                </FormInput>
            </div>

            <FormRichText id="introduction" label="Introduction" class="mb-4" :error="errors.description" v-model="form.description">
                <template v-slot:help>
                    <p class="font-semibold mb-4">This area appears at the top of your specification and is intended to provide a high-level overview of the project.</p>
                    <p>Consider including:</p>
                    <ul class="list-disc italic ml-6">
                        <li>What the project hopes to achieve.</li>
                        <li>The justification for the project.</li>
                        <li>Any specific technologies one expects to use.</li>
                        <li>Expected timescales for key milestones.</li>
                    </ul>
                </template>
            </FormRichText>
        </form>
    </SidepanelLayout>
</template>

<script>
import FormInput from '@/components/forms/FormInput.vue';
import FormRichText from '@/components/forms/FormRichText.vue';
import IconSet from '@/components/IconSet.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import ProjectDelete from '@/components/modals/ProjectDelete.vue';
import SidepanelLayout from '@/components/layouts/SidepanelLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import Tooltip from '@/components/Tooltip.vue';
import TrackDirty from '@/mixins/TrackDirty';
import UniqueId from '@/mixins/UniqueId';
import Project from '@/stores/models/Project';
import { useAlertsStore, useModalStore } from '@/stores';

export default {
    inject: ['api'],
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        FormInput,
        FormRichText,
        IconSet,
        SidepanelLayout,
        SpinnerButton,
        Tooltip,
    },
    data() {
        const project = Project.repository().find(this.project_id);

        return {
            form: {
                description: project.description,
                name: project.name,
            },
            errors: {},
            is_waiting: false
        };
    },
    methods: {
        openProjectDeleteModal() {
            useModalStore().open(ProjectDelete, {project: this.project});
        },
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('projects/' + this.project_id + '/edit', this.form)
                .then((result) => {
                    Project.repository().save(result.data);

                    this.setCleanForm();

                    this.$router.push({ name: 'projects.show', params: {project_id: this.project_id}});

                    useAlertsStore().push('Project updated.');
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        TrackDirty,
        UniqueId
    ],
    props: {
        'project_id': {
            'type': String,
            'required': true
        }
    },
    async setup(props) {
        return {
            project: Project.repository().find(props.project_id),
        };
    },
};
</script>
