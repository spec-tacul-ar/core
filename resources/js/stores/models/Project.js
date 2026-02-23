import Filters from '../Filters';
import Model from '../Model';

export default class Project extends Model {
    static repository_name = 'projects';

    constructor(data) {
        super(data);

        this.filters = new Filters(this.id);
    }

    get features() {
        return this.constructor.repository('features').collection.where('project_id', this.id);
    }
    set features(features) {
        this.constructor.repository('features').saveMany(features);
    }

    get features_estimate() {
        return this.features.sum('requirements_estimate');
    }
    
    get total_estimate() {
        return this.features_estimate;
    }

    get users() {
        return this.constructor.repository('users').collection.where('project_id', this.id);
    }
    set users(users) {
        this.constructor.repository('users').saveMany(users);
    }
}
