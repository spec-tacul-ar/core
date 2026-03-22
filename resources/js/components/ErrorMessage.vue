<template>
    <div class="h-full flex justify-center items-center">
        <div class="flex items-center">
            <div class="text-gray-600 pr-4 border-r-2">
                <IconSet :name="icon" class="w-16 h-16" />
            </div>

            <div class="pl-4">
                <h1 class="font-display font-semibold text-4xl">{{ title }}</h1>
                
                <p>
                    <slot>An unknown error occured.</slot>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    props: {
        icon: {
            type: String,
            default: 'danger'
        },
        title: {
            type: String,
            default: 'Error',
        },
    },
};
</script>
