export default {
    state: () => {
        return {
            alerts: []
        };
    },
    actions: {
        push (text, type = 'success') {
            const duplicate = this.alerts.find(alert => alert.text === text && alert.type === type);

            if (!duplicate) {
                this.alerts.push({
                    type: type,
                    text: text
                });
            }
        },
        shift () {
            this.alerts.shift();
        }
    }
};
