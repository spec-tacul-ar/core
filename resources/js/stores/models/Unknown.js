import Model from '../Model';

export default class Unknown extends Model {
    static repository_name = 'unknowns';

    get requirement() {
        return this.constructor.repository('requirements').find(this.requirement_id);
    }
}
