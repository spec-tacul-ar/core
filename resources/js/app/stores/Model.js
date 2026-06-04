export default class {
    static repositories = {};

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

    static resetRepositories() {
        Object.values(this.repositories).forEach(repository => repository().$reset());
    }
}
