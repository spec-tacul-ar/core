<template>
    <DefaultLayout>
        <template v-slot:menu>
            <RouterLink :to="{ name: 'projects.index' }" class="link flex items-center gap-2 mr-auto">
                <IconSet name="chevron-left" />

                Dashboard
            </RouterLink>
        </template>

        <form @submit.prevent="submit" class="max-w-xl mx-auto">
            <h1 class="font-display font-semibold text-4xl mb-4">Create project</h1>

            <Card class="p-8 mb-4">
                <FormInput v-model="form.name" name="name" label="What is your project called?" :error="errors.name" required autofocus>
                    <template v-slot:prepend>
                        <p class="mb-4">A working title is fine; you can always change it later.</p>
                    </template>
                </FormInput>
            </Card>

            <Card class="p-8 mb-4">
                <label for="id" class="font-bold mb-1 mr-auto">Who is going to use the end product?</label>
                <p class="mb-4">These are the types of users - not individuals. We've given a few examples. Feel free to change them.</p>

                <div class="space-y-2">
                    <div v-for="(actor, index) in form.actors" :key="actor.id" class="flex mx-auto">
                        <FormInput name="user" :id="'actor_' + actor.id" v-model="actor.name" @keydown.enter.prevent="form.actors.length === index + 1 ? addActor() : null" />
                    
                        <button v-if="form.actors.length === index + 1" type="button" class="p-2" @click="addActor()"><IconSet name="plus-lg" class="size-6" /></button>
                        <button v-else @click="removeActor(index)" type="button" class="p-2 text-red-400"><IconSet name="x" class="size-6" /></button>
                    </div>
                </div>
            </Card>

            <Card class="p-8 mb-8">
                <label for="id" class="font-bold mb-1 mr-auto">What are the main features of your project?</label>
                <p class="mb-4">These are the large sections of functionality. Again, we've given examples for you to tweak.</p>

                <div class="space-y-2">
                    <div v-for="(feature, index) in form.features" :key="feature.id" class="flex mx-auto">
                        <FormInput name="feature" :id="'feature_' + feature.id" v-model="feature.name" @keydown.enter.prevent="form.features.length === index + 1 ? addFeature() : null" />

                        <button v-if="form.features.length === index + 1" type="button" class="p-2" @click="addFeature()"><IconSet name="plus-lg" class="size-6" /></button>
                        <button v-else @click="removeFeature(index)" type="button" class="p-2 text-red-400"><IconSet name="x" class="size-6" /></button>
                    </div>
                </div>
            </Card>

            <div class="flex justify-center items-center gap-4">
                <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting">Create project</SpinnerButton>
            </div>
        </form>
    </DefaultLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import FormInput from '@/components/forms/FormInput.vue';
import IconSet from '@/components/IconSet.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import DefaultLayout from '@/components/layouts/DefaultLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import TrackDirty from '@/mixins/TrackDirty';
import UniqueId from '@/mixins/UniqueId';
import Project from '@/stores/models/Project';
import { useAlertsStore } from '@/stores';

export default {
    inject: ['api'],
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        Card,
        FormInput,
        IconSet,
        DefaultLayout,
        SpinnerButton
    },
    data() {
        return {
            'form': {
                features: [
                    { id: 0, name: 'User Authentication' },
                    { id: 1, name: 'Invoicing' },
                    { id: 2, name: '' },
                ],
                name: '',
                actors: [
                    { id: 0, name: 'Members' },
                    { id: 1, name: 'Staff' },
                    { id: 2, name: '' },
                ],
            },
            'errors': {},
            'is_waiting': false
        };
    },
    methods: {
        addFeature() {
            const next_id = this.form.features.reduce((carry, item) => Math.max(carry, item.id), 0) + 1;

            this.form.features.push({
                id: next_id,
                name: '',
            });

            this.$nextTick(() => document.getElementById('feature_' + next_id).focus());
        },
        addActor() {
            const next_id = this.form.actors.reduce((carry, item) => Math.max(carry, item.id), 0) + 1;

            this.form.actors.push({
                id: next_id,
                name: '',
            });

            this.$nextTick(() => document.getElementById('actor_' + next_id).focus());
        },
        removeFeature(index) {
            this.form.features.splice(index, 1);
        },
        removeActor(index) {
            this.form.actors.splice(index, 1);
        },
        submit() {
            this.errors = {};
            this.is_waiting = true;

            const data = {
                ...this.form,
                features: this.form.features.map(item => item.name).map(item => item.trim()).filter(item => item.length > 0),
                actors: this.form.actors.map(item => item.name).map(item => item.trim()).filter(item => item.length > 0),
            };

            this.api.post('projects', data)
                .then((result) => {
                    const project = result.data;

                    Project.repository().save(project);

                    this.setCleanForm();

                    useAlertsStore().push('Project created.');

                    this.$router.push({ name: 'projects.show', params: {project_id: project.id}});
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
};
</script>
