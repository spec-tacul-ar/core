<template>
    <SidepanelLayout help="/docs/projects">
        <template #toolbar>
            <Tooltip text="Delete project">
                <button type="button" class="p-2" @click="openProjectDeleteModal()">
                    <IconSet name="trash" class="text-red-400 dark:text-red-300 size-6" />
                </button>
            </Tooltip>

            <Tooltip v-if="!project.archived_at" text="Archive project">
                <button type="button" class="p-2" :disabled="is_archiving" @click="archive()">
                    <IconSet name="archive" class="text-gray-600 size-6 dark:text-gray-300" />
                </button>
            </Tooltip>

            <EditingWarning :editors="other_editors" class="ml-auto" />
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

            <FormOptions
                type="select"
                :id="elementId('locale')"
                label="Locale"
                class="mb-4"
                :error="errors.locale"
                :options="locales"
                v-model="form.locale"
                required>
                <template v-slot:help>
                    <p class="font-semibold mb-4">The locale controls the language used for headings and labels within the specification.</p>
                    <p>Changing this will not translate the specification.</p>
                </template>
            </FormOptions>

        </form>
    </SidepanelLayout>
</template>

<script>
import EditingWarning from '@/components/EditingWarning.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormOptions from '@/components/forms/FormOptions.vue';
import FormRichText from '@/components/forms/FormRichText.vue';
import EditingPresence from '@/mixins/EditingPresence';
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
        EditingWarning,
        FormInput,
        FormOptions,
        FormRichText,
        IconSet,
        SidepanelLayout,
        SpinnerButton,
        Tooltip,
    },
    computed: {
        editing_channel() {
            return 'projects.editing.' + this.project_id;
        },
    },
    data() {
        const project = Project.repository().find(this.project_id);

        return {
            form: {
                description: project.description,
                locale: project.locale,
                name: project.name,
            },
            locales: [
                { id: 'da', name: 'Danish' },
                { id: 'nl', name: 'Dutch' },
                { id: 'en', name: 'English' },
                { id: 'fr', name: 'French' },
                { id: 'de', name: 'German' },
                { id: 'it', name: 'Italian' },
                { id: 'pl', name: 'Polish' },
                { id: 'pt', name: 'Portuguese' },
                { id: 'es', name: 'Spanish' },
            ],
            errors: {},
            is_archiving: false,
            is_waiting: false
        };
    },
    methods: {
        archive() {
            this.is_archiving = true;

            this.api.post('projects/' + this.project_id + '/archive')
                .then((result) => {
                    Project.repository().save(result.data);

                    this.$router.push({ name: 'projects.index' });

                    useAlertsStore().push('Project archived.');
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_archiving = false);
        },
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
        EditingPresence,
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
