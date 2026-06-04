export default {
    computed: {
        is_dirty() {
            if (!this.clean_form_json) {
                return false;
            }

            return JSON.stringify(this.form) !== this.clean_form_json;
        }
    },
    data: function () {
        return {
            clean_form_json: null
        };
    },
    methods: {
        confirmClose: () => confirm('You have unsaved changes. Continue?'),
        setCleanForm: function (form) {
            if (!form) {
                form = this.form;
            }

            this.clean_form_json = JSON.stringify(form);
        },
        unloadCheck(event) {
            if (!this.is_dirty) {
                return true;
            }

            event.preventDefault();

            return event.returnValue = 'Are you sure you want to leave? You might lose unsaved work.';
        },
    },
    mounted() {
        this.setCleanForm();

        addEventListener('beforeunload', this.unloadCheck);
    },
    unmounted() {
        this.setCleanForm(this.form);

        removeEventListener('beforeunload', this.unloadCheck);
    },
};
