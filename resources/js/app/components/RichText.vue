<template>
    <div>
        <div class="prose" v-html="purified"></div>
    </div>
</template>

<script>
import DOMPurify from 'dompurify';

export default {
    props: ['markup'],
    computed: {
        purified () {
            return this.markup ? DOMPurify.sanitize(this.markup) : '';
        }
    },
};
</script>
