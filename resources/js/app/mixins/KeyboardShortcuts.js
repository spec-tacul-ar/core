export default {
    methods: {
        keydown(event) {
            const keyName = event.key;

            if (keyName === 'Meta') {
                return;
            }

            if (event.metaKey && keyName === 'Enter') {
                const element = this.$el.querySelector('form');

                if (element) {
                    element.requestSubmit();
                }
            }
        }
    },
    mounted() {
        addEventListener('keydown', this.keydown);
    },
    unmounted() {
        removeEventListener('keydown', this.keydown);
    }
};
