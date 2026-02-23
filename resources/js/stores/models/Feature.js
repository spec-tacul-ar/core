import Model from '../Model';

export default class Feature extends Model {
    static repository_name = 'features';

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
}
