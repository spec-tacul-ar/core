import Model from '@/stores/Model';

export default class Contributor extends Model {
    static repository_name = 'contributors';

    get project() {
        return this.constructor.repository('projects').find(this.project_id);
    }

    get role_name() {
        return {
            owner: 'Owner',
            editor: 'Editor',
            viewer: 'Viewer'
        }[this.role];
    }
}
