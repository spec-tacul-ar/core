<template>
    <div class="mt-4">
        <div>
            <div class="flex items-center gap-2 mx-4 mb-2">
                <h4 class="uppercase font-semibold mr-auto">Statuses</h4>

                <button
                    v-if="filters.has_statuses"
                    type="button"
                    class="p-1"
                    @click="filters.clearStatuses()">

                    <IconSet name="x-lg" class="size-4" />
                </button>
            </div>

            <div class="mb-4">
                <FilterSwitch
                    :value="filters.statuses.completed"
                    @change="filters.setFilter('statuses', 'completed', $event)">

                    Completed
                </FilterSwitch>

                <FilterSwitch
                    :value="filters.statuses.blocked"
                    @change="filters.setFilter('statuses', 'blocked', $event)">

                    Blocked
                </FilterSwitch>

                <FilterSwitch
                    :value="filters.statuses.estimated"
                    @change="filters.setFilter('statuses', 'estimated', $event)">

                    Estimated
                </FilterSwitch>

                <FilterSwitch
                    :value="filters.statuses.has_tasks"
                    @change="filters.setFilter('statuses', 'has_tasks', $event)">

                    Has tasks
                </FilterSwitch>

                <FilterSwitch
                    :value="filters.statuses.has_unknowns"
                    @change="filters.setFilter('statuses', 'has_unknowns', $event)">

                    Has unknowns
                </FilterSwitch>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 mx-4 mb-2">
                <h4 class="uppercase font-semibold mr-auto">Users</h4>

                <button
                    v-if="filters.has_users"
                    type="button"
                    class="p-1"
                    @click="filters.clearUsers()">

                    <IconSet name="x-lg" class="size-4" />
                </button>
            </div>

            <div class="mb-4">
                <FilterSwitch
                    v-for="user in project.users.sortBy('weight')"
                    :key="user.id"
                    :value="filters.users[user.id]"
                    @change="filters.setFilter('users', user.id, $event)">

                    {{ user.name }}
                </FilterSwitch>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 mx-4 mb-2">
                <h4 class="uppercase font-semibold mr-auto">Features</h4>

                <button
                    v-if="filters.has_features"
                    type="button"
                    class="p-1"
                    @click="filters.toggleFeatureMode()">

                    <IconSet name="plus-slash-minus" class="size-4" />
                </button>

                <button
                    v-if="filters.has_features"
                    type="button"
                    class="p-1"
                    @click="filters.clearFeatures()">

                    <IconSet name="x-lg" class="size-4" />
                </button>
            </div>

            <div class="mb-4">
                <button
                    v-for="feature in project.features.sortBy('weight')"
                    :key="feature.id"
                    type="button"
                    class="w-full flex items-center gap-2 text-left hover:bg-gray-50 px-4 py-2"
                    :class="filters.features.includes(feature.id) ? '' : 'pl-10'"
                    @click="filters.toggleFeature(feature.id)">

                    <IconSet v-if="filters.features.includes(feature.id) && !filters.exclude_features" name="plus-lg" class="size-4 shrink-0" />
                    <IconSet v-if="filters.features.includes(feature.id) && filters.exclude_features" name="minus-lg" class="size-4 shrink-0" />

                    {{ feature.name }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import FilterSwitch from '@/components/FilterSwitch.vue';
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        FilterSwitch,
        IconSet,
    },
    computed: {
        filters() {
            return this.project.filters;
        },
    },
    props: ['project'],
};
</script>
