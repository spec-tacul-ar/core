<template>
    <div class="flex">
        <label v-if="label" :for="id" class="font-bold mb-1">{{ label }}&nbsp;<span v-if="required" class="text-red-400">*</span></label>

        <InfoPopover v-if="has_help_slot" class="ml-auto">
            <slot name="help"></slot>
        </InfoPopover>
    </div>

    <template v-if="this.type === 'select'">
        <select
            class="w-full"
            :id="id"
            :class="{ 'border-red-400': error }"
            :required="required"
            v-bind="$attrs"
            v-bind:value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)">

            <option :value="null"></option>
            <option :value="option.id" v-for="option in options" :key="option.id">{{ option.name }}</option>
        </select>
    </template>
    <template v-else>
        <div class="flex flex-wrap gap-x-4 gap-y-1">
            <div v-for="option in options" :key="option.id" class="flex items-center" :class="{ 'border-red-400': error }">
                <input
                    :type="type"
                    :id="id + '_' + option.id"
                    :name="id + '_' + option.id"
                    class="text-black outline-black"
                    :class="{ 'border-red-400': error }"
                    :value="option.id"
                    v-bind="$attrs"
                    v-model="model" />

                <label :for="id + '_' + option.id" class="pl-2">{{ option.name }}</label>
            </div>
        </div>
    </template>

    <div v-if="error" class="text-red-400">{{ error[0] }}</div>
</template>

<script>
import InfoPopover from '@/components/InfoPopover.vue';

export default {
    components: {
        InfoPopover
    },
    computed: {
        has_help_slot() {
            return this.$slots.help;
        },
        model: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },
    inheritAttrs: false,
    props: {
        'error': Array,
        'id': String,
        'inline': Boolean,
        'label': String,
        'modelValue': {
            validator() {
                return true;
            },
        },
        'options': Array,
        'required': Boolean,
        'type': String
    },
};
</script>
