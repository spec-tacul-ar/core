<template>
    <SidepanelLayout>
        <template #buttons>
            <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting" @click="submit">Save</SpinnerButton>
        </template>

        <form @submit.prevent="submit">
            <h2 class="text-4xl font-light mb-4">Organise</h2>

            <p class="mb-4">Here you can reorganise the items in your specification.</p>

            <fieldset class="mb-4">
                <legend class="text-xl mb-2">Users</legend>

                <Draggable 
                    v-model="actors" 
                    group="actors" 
                    item-key="id"
                    tag="ul"
                    handle=".handle"
                    class="space-y-2">

                    <template #item="{element}">
                        <li class="flex items-center border border-gray-100">
                            <div class="handle cursor-grab px-2 py-2">
                                <IconSet name="grip-vertical" class="size-6" />
                            </div>

                            {{ element.name }}
                        </li>
                    </template>
                </draggable>
            </fieldset>

            <fieldset class="mb-4">
                <legend class="text-xl mb-2">Features and requirements</legend>

                <Draggable
                    v-model="features"
                    group="features" 
                    item-key="id"
                    handle=".handle"
                    class="space-y-2">

                    <template #item="{element}">
                        <div class="mb-2 border border-gray-100">
                            <div class="flex items-center">
                                <div class="handle cursor-grab px-2 py-2">
                                    <IconSet name="grip-vertical" class="size-6" />
                                </div>

                                {{ element.name }}

                                <button type="button" class="ml-auto mr-2" @click="toggleFeature(element.id)">
                                    <IconSet name="chevron-down" v-if="!expanded.includes(element.id)" />
                                    <IconSet name="chevron-up" v-if="expanded.includes(element.id)" />
                                </button>
                            </div>
                            <div :class="expanded.includes(element.id) ? '' : 'hidden'">
                                <Draggable
                                    v-model="element.requirements" 
                                    group="requirements" 
                                    item-key="id"
                                    tag="ul"
                                    handle=".handle2">
                                    
                                    <template #item="{element}">
                                        <li class="flex items-center gap-3 px-3 py-2">
                                            <IconSet name="grip-horizontal" class="handle2 size-4 cursor-grab shrink-0" />
                                            <span class="text-sm">{{ element.short_title }}</span>
                                        </li>
                                    </template>
                                </Draggable>
                            </div>
                        </div>
                    </template>
                </draggable>
            </fieldset>
        </form>
    </SidepanelLayout>
</template>

<script>
import Draggable from 'vuedraggable';
import SidepanelLayout from '@/components/layouts/SidepanelLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import TrackDirty from '@/mixins/TrackDirty';
import IconSet from '@/components/IconSet.vue';
import Feature from '@/stores/models/Feature';
import Project from '@/stores/models/Project';
import Requirement from '@/stores/models/Requirement';
import Actor from '@/stores/models/Actor';
import { useAlertsStore } from '@/stores';

export default {
    inject: ['api'],
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        Draggable,
        IconSet,
        SidepanelLayout,
        SpinnerButton,
    },
    computed: {
        form() {
            return {
                'actors': this.actors.map((actor, index) => ({id: actor.id, weight: index})),
                'features': this.features.map((feature, index) => ({id: feature.id, weight: index})),
                'requirements': this.features.map(feature => {
                    return feature.requirements.map((requirement, index) => ({feature_id: feature.id, id: requirement.id, weight: index}));
                }).flat(),
            };
        },
    },
    data() {
        const project = Project.repository().find(this.project_id);

        const features = project.features.sortBy('id').sortBy('weight').map(feature => {
            return {
                ...feature,
                requirements: feature.requirements.sortBy('id').sortBy('weight').all(),
            };
        }).all();

        return {
            'expanded': [],
            'actors': project.actors.sortBy('id').sortBy('weight').all(),
            'features': features,
            'is_waiting': false,
            'original': null,
        };
    },
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('projects/' + this.project_id + '/organise', this.form)
                .then((result) => {
                    Feature.repository().saveMany(result.data.features);
                    Requirement.repository().saveMany(result.data.requirements);
                    Actor.repository().saveMany(result.data.actors);

                    this.setCleanForm();

                    this.$router.push({ name: 'projects.show', params: {project_id: this.project_id}});

                    useAlertsStore().push('Project saved.');
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        },
        toggleFeature(id) {
            const index = this.expanded.indexOf(id);

            if (index === -1) {
                this.expanded.push(id);
            } else {
                this.expanded.splice(index, 1);
            }
        },
    },
    mixins: [
        TrackDirty,
    ],
    props: {
        'project_id': {
            'type': Number,
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
