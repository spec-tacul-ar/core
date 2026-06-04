<template>
    <label class="font-semibold mb-1 mr-auto"><slot />&nbsp;<span v-if="required" class="text-red-400">*</span></label>
</template>

<script>
export default {
    props: {
        'required': Boolean,
    },
};
</script>
