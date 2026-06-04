import Model from '@/stores/Model';

export default class Collaboration extends Model {
    static repository_name = 'collaborations';

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
