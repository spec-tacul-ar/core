<template>
    <template v-if="['radio', 'checkbox'].includes(this.type)">
        <div class="flex">
            <div class="flex items-center mr-auto">
                <input
                    :type="input_type"
                    :id="id"
                    class="text-black border-gray-300 ring-black ring-0 focus:ring-offset-0 focus:ring-1"
                    :class="{ 'is-invalid': error, 'rounded-sm': input_type.toLowerCase() === 'checkbox' }"
                    v-bind="$attrs"
                    v-model="checked"
                    @input="$emit('update:modelValue', checked)" />

                <label :for="id" class="pl-2">{{ label }}&nbsp;<span class="text-red-400" v-if="required">*</span></label>
            </div>

            <slot name="other"></slot>

            <InfoPopover v-if="has_help_slot">
                <slot name="help"></slot>
            </InfoPopover>
        </div>
    </template>
    <template v-else>
        <!-- TODO Think about allowing before and after slots. This might make the component too complicated. -->

        <div v-if="label || has_help_slot" class="w-full flex">
            <FormLabel :for="id" v-if="label" :required="required">{{ label }}</FormLabel>

            <slot name="other"></slot>

            <InfoPopover v-if="has_help_slot">
                <slot name="help"></slot>
            </InfoPopover>
        </div>
        
        <div class="relative grow">
            <slot name="prepend"></slot>

            <component :is="element"
                :type="input_type"
                :id="id"
                :list="options ? id + '_options' : null"
                class="w-full block border-gray-300 rounded-sm"
                :class="error ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-black' "
                :required="required"
                v-bind="$attrs"
                v-bind:value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)" />

            <slot name="append"></slot>
        </div>
    </template>

    <datalist v-if="options" :id="id + '_options'">
        <option v-for="option in options" :key="option" :value="option"></option>
    </datalist>

    <div v-if="error" class="text-red-400">{{ error[0] }}</div>
</template>

<script>
import InfoPopover from '@/components/InfoPopover.vue';
import FormLabel from '@/components/forms/FormLabel.vue';

export default {
    components: {
        FormLabel,
        InfoPopover,
    },
    computed: {
        element() {
            if (['textarea'].includes(this.type)) {
                return this.type;
            }

            return 'input';
        },
        has_help_slot() {
            return this.$slots.help;
        },
        input_type() {
            return ['textarea'].includes(this.type) ? null : this.type;
        }
    },
    data() {
        let value = false;

        if (typeof this.modelValue === 'string') {
            value = ['1', 'true', 'on'].includes(this.modelValue);
        } else if (typeof this.modelValue === 'number') {
            value = this.modelValue === 1;
        } else {
            value = !!this.modelValue;
        }

        return {
            'checked': value
        };
    },
    inheritAttrs: false,
    props: {
        'error': Array,
        'id': String,
        'label': String,
        'modelValue': {
            'validator': (value) => {
                return ['boolean', 'string', 'number'].includes(typeof value);
            }
        },
        'options': Array,
        'required': Boolean,
        'type': String
    },
    watch: {
        modelValue() {
            this.checked = this.modelValue;
        }
    }
};
</script>
