import Model from '@/stores/Model';

export default class Comment extends Model {
    static repository_name = 'comments';

    get project() {
        return this.constructor.repository('projects').find(this.project_id);
    }

    get commentable() {
        switch(this.commentable_type) {
            case 'requirement':
                return this.constructor.repository('requirements').find(this.commentable_id).title;
            case 'feature':
                return this.constructor.repository('features').find(this.commentable_id).name;
            default:
                return null;
        }
    }
}
