<template>
    <SidepanelLayout>
        <template #buttons>
            <div class="flex items-center gap-4">
                <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting" @click="submit">Save</SpinnerButton>
                <FormInput v-if="is_creating" type="checkbox" :id="elementId('add_another')" label="Create another after saving" v-model="add_another" />
            </div>
        </template>

        <form @submit.prevent="submit">
            <h2 class="text-4xl font-light mb-4">
                {{ is_creating ? 'Create feature' : 'Edit feature' }}
            </h2>

            <ValidationMessages :errors="errors" />

            <p class="mb-4">A feature is a group of requirements, sometimes known as an <em>epic</em>.</p>

            <div class="mb-4">
                <form-input type="text" :id="elementId('name')" label="Name" :error="errors.name" v-model="form.name" required>
                    <template v-slot:help>
                        <p class="font-semibold mb-4">A label for the feature.</p>
                        <p>Examples:</p>
                        <ul class="list-disc italic ml-6">
                            <li>Notifications</li>
                            <li>User authentication</li>
                            <li>User management</li>
                        </ul>
                    </template>
                </form-input>
            </div>
            <div>
                <FormRichText :id="elementId('description')" label="Description" :heading="4" :error="errors.description" v-model="form.description">
                    <template v-slot:help>
                        <p class="font-semibold mb-4">Use this field to give a high-level summary of the feature.</p>
                        <p>Try to include:</p>
                        <ul class="list-disc italic ml-6">
                            <li>Justification for this feature.</li>
                            <li>Possible approaches.</li>
                            <li>Scope of this feature.</li>
                        </ul>
                    </template>
                </FormRichText>
            </div>
        </form>
    </SidepanelLayout>
</template>

<script>
import FormInput from '@core/components/forms/FormInput.vue';
import FormRichText from '@core/components/forms/FormRichText.vue';
import KeyboardShortcuts from '@core/mixins/KeyboardShortcuts';
import SidepanelLayout from '@core/components/layouts/SidepanelLayout.vue';
import SpinnerButton from '@core/components/SpinnerButton.vue';
import TrackDirty from '@core/mixins/TrackDirty';
import UniqueId from '@core/mixins/UniqueId';
import ValidationMessages from '@core/components/ValidationMessages.vue';
import Feature from '@core/stores/models/Feature';
import { useAlertsStore } from '@core/stores';
import { inject } from 'vue';

export default {
    inject: ['api'],
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        FormInput,
        FormRichText,
        SidepanelLayout,
        SpinnerButton,
        ValidationMessages,
    },
    computed: {
        is_creating() {
            return !this.feature_id;
        },
    },
    data() {
        return {
            'add_another': false,
            'form': {
                name: this.feature?.name,
                description: this.feature?.description,
            },
            'errors': {},
            'is_waiting': false
        };
    },
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            const endpoint = this.is_creating ? 'features/add' : 'features/' + this.feature_id + '/edit';
            const data = this.is_creating ? {...this.form, 'project_id': this.project_id} : this.form;

            this.api.post(endpoint, data)
                .then((result) => {
                    const feature = {...result.data, was_recently_created: this.is_creating};

                    if (this.is_creating) {
                        Feature.repository().save(feature);
                    } else {
                        Feature.repository().save(feature);
                    }

                    useAlertsStore().push('Feature saved.');

                    if (this.add_another) {
                        this.form.name = '';
                        this.form.description = '';
                    }

                    this.setCleanForm();

                    if (!this.add_another) {
                        this.$router.push({ name: 'projects.show', params: { project_id: feature.project_id }, hash: '#feature_' + feature.id });
                    }
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
            'type': Number,
            'required': true,
        },
        'feature_id': {
            'type': Number,
            'required': false,
        },
    },
    async setup(props) {
        if (!props.feature_id) {
            return;
        }

        const api = inject('api');
        const repository = Feature.repository();
        let feature = repository.find(props.feature_id);

        if (!feature) {
            const result = await api.get('features/' + props.feature_id + '/read');
            feature = repository.save(result.data);
        }

        return {
            feature,
        };
    },
};
</script>
