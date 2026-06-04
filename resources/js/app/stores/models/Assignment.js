import Model from '../Model';

export default class Assignment extends Model {
    static repository_name = 'assignments';

    get actor() {
        return this.constructor.repository('actors').find(this.actor_id);
    }

    get requirement() {
        return this.constructor.repository('requirements').find(this.requirement_id);
    }
}
