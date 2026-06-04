import { markRaw } from 'vue';

export default {
    state: () => {
        return {
            'component': null,
            'props': {},
        };
    },
    actions: {
        open (component, props = {}) {
            const raw_component = markRaw(component);

            this.component = raw_component;
            this.props = props;
        },
        close () {
            this.component = null;
            this.props = {};
        },
    },
    getters: {
        is_open: (state) => state.component !== null,
    },
};
