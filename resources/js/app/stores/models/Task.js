import Model from '../Model';

export default class Task extends Model {
    static repository_name = 'tasks';

    get requirement() {
        return this.constructor.repository('requirements').find(this.requirement_id);
    }
}
