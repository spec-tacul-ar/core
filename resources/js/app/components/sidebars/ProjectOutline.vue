<template>
    <ol class="p-4 space-y-4">
        <li><a href="#introduction" class="uppercase font-semibold" @click.prevent="scroll">Introduction</a></li>
        <li><a href="#users" class="uppercase font-semibold" @click.prevent="scroll">Users</a></li>
        <li><a href="#features" class="uppercase font-semibold" @click.prevent="scroll">Features</a></li>
        <li v-for="feature in this.project.features.sortBy('weight')" :key="feature.id">
            <a :href="'#feature_' + feature.id" class="block uppercase mb-3" @click.prevent="scroll">{{ feature.name }}</a>

            <ol class="space-y-4">
                <li v-for="requirement in sortRequirements(feature.requirements)" :key="requirement.id" class="flex items-center gap-2">
                    <div v-if="requirement.is_blocked" class="size-2 shrink-0 rounded-full bg-red-400 dark:bg-red-950"></div>
                    <div v-else-if="requirement.unknowns.isNotEmpty()" class="size-2 shrink-0 rounded-full bg-orange-400"></div>
                    <div v-else-if="requirement.is_complete" class="size-2 shrink-0 rounded-full bg-green-400 dark:bg-green-950"></div>
                    <div v-else class="size-2 shrink-0"></div>

                    <a :href="'#requirement_' + requirement.id" class="text-sm leading-tight" @click.prevent="scroll">{{ formatName(requirement.name) }}</a>
                </li>
            </ol>
        </li>
    </ol>
</template>

<script>
import collect from 'collect.js';

export default {
    methods: {
        formatName: name => name.charAt(0).toUpperCase() + name.substring(1),
        sortRequirements: requirements => collect(requirements).sortBy('weight'),
        scroll(event) {
            const href = event.currentTarget.getAttribute('href');
            const hash = href.split('#')[1];

            const element = document.getElementById(hash);

            if (element) {
                window.location.hash = hash; // history.replaceState() upsets Vue Router
            
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });

                this.$emit('navigation');
            }
        },
    },
    props: ['project']
};
</script>
