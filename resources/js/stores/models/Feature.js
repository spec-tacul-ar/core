import Model from '../Model';

export default class Feature extends Model {
    static repository_name = 'features';

    get comments() {
        return this.constructor.repository('comments').collection.where('commentable_type', 'feature').where('commentable_id', this.id);
    }
    set comments(comments) {
        this.constructor.repository('comments').saveMany(comments);
    }

    get has_tasks () {
        return this.requirements.some(requirement => requirement.has_tasks);
    }

    get project() {
        return this.constructor.repository('projects').find(this.project_id);
    }

    get requirements() {
        return this.constructor.repository('requirements').collection.where('feature_id', this.id);
    }
    set requirements(requirements) {
        this.constructor.repository('requirements').saveMany(requirements);
    }
    
    get requirements_estimate () {
        return this.requirements.reduce((total, requirement) => total + requirement.tasks_estimate, 0);
    }

    onDelete() {
        const comment_ids = this.comments.pluck('id').toArray();

        this.constructor.repository('comments').deleteMany(comment_ids);
    }
}
