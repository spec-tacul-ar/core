<template>
    <div class="multiline-text" v-html="markup"></div>
</template>

<script>
export default {
    props: ['text'],
    computed: {
        markup () {
            const escaped = this.text.trim().replace(/[&<>'"]/g, tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag]));

            const markup = escaped
                .split('\n\n')
                .join('</p><p>')
                .split('\n')
                .join('<br>');

            return '<p>' + markup + '</p>';
        }
    }
};
</script>
