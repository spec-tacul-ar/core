import Model from '@/stores/Model';

export default class Invitation extends Model {
    static repository_name = 'invitations';

    get project() {
        return this.constructor.repository('projects').find(this.project_id);
    }
    set project(project) {
        this.constructor.repository('projects').save(project);
    }

    get role_name() {
        return {
            owner: 'Owner',
            editor: 'Editor',
            viewer: 'Viewer'
        }[this.role];
    }
}
