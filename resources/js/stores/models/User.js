import Model from '../Model';

export default class User extends Model {
    static repository_name = 'users';

    get project() {
        return this.constructor.repository('projects').find(this.project_id);
    }

    get requirements() {
        return this.constructor.repository('requirements').collection
            .filter(requirement =>requirement.assignments.pluck('user_id').contains(this.id));
    }
}
