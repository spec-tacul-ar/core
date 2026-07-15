import { useAuthStore } from '@/stores';

export default {
    inject: ['echo'],
    computed: {
        other_editors() {
            const account = useAuthStore().account;

            return this.editors.filter(editor => editor.id !== account?.id);
        },
    },
    data() {
        return {
            editors: [],
            channel: null,
        };
    },
    mounted() {
        this.channel = this.editing_channel;

        if (!this.channel) {
            return;
        }

        this.echo.join(this.channel)
            .here(users => this.editors = users)
            .joining(user => {
                if (!this.editors.some(editor => editor.id === user.id)) {
                    this.editors.push(user);
                }
            })
            .leaving(user => this.editors = this.editors.filter(editor => editor.id !== user.id));
    },
    unmounted() {
        if (this.channel) {
            this.echo.leave(this.channel);
        }
    },
};
