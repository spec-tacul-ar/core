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
            name = this.name.toLowerCase() + 's'; // TODO Use a pluraliser.
        }

        return this.repositories[name]();
    }
}
