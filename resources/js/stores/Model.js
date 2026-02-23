export default class {
    static repositories = null;

    constructor(data) {
        Object.assign(this, data);
    }

    is(other) {
        return this.constructor === other.constructor && this.id === other.id;
    }

    static repository(name = null) {
        if (!name) {
            name = this.repository_name;
        }

        return this.repositories[name]();
    }
}
