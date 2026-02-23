import Model from '../Model';

export default class Assignment extends Model {
    static repository_name = 'assignments';

    get user() {
        return this.constructor.repository('users').find(this.user_id);
    }

    get requirement() {
        return this.constructor.repository('requirements').find(this.requirement_id);
    }
}
