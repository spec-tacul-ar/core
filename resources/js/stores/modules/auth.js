export default {
    state: () => {
        return {
            account: null,
            is_logged_in: false,
            resume_session: true,
        };
    },
    actions: {
        setAccount(account) {
            this.account = account;
            this.is_logged_in = !!account;
        },
        clearAccount() {
            this.setAccount(null);
        },
    },
    getters: {
        is_solo: (state) => state.is_logged_in && !state.account.id,
    },
};
