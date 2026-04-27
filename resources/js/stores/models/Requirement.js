import Model from '../Model';

export default class Requirement extends Model {
    static repository_name = 'requirements';

    get assignments() {
        return this.constructor.repository('assignments').collection.where('requirement_id', this.id);
    }
    set assignments(assignments) {
        this.constructor.repository('assignments').saveMany(assignments);
    }
    
    get comments() {
        return this.constructor.repository('comments').collection.where('commentable_type', 'requirement').where('commentable_id', this.id);
    }
    set comments(comments) {
        this.constructor.repository('comments').saveMany(comments);
    }

    get feature() {
        return this.constructor.repository('features').find(this.feature_id);
    }

    get has_unknowns() {
        return this.unknowns.isNotEmpty();
    }

    get has_tasks() {
        return this.tasks.isNotEmpty();
    }

    get is_complete() {
        return this.has_tasks && this.tasks.every(task => task.is_complete);
    }

    get is_blocked() {
        return this.blocked_reason !== null;
    }

    get unknowns() {
        return this.constructor.repository('unknowns').collection.where('requirement_id', this.id);
    }
    set unknowns(unknowns) {
        this.constructor.repository('unknowns').saveMany(unknowns);
    }

    get short_title() {
        return this.title.charAt(0).toUpperCase() + this.title.slice(1);
    }

    get tasks() {
        return this.constructor.repository('tasks').collection.where('requirement_id', this.id);
    }
    set tasks(tasks) {
        this.constructor.repository('tasks').saveMany(tasks);
    }

    get title() {
        const actors = this.assignments.map(assignment => assignment.actor.name);

        if (actors.isEmpty()) {
            return 'Users can ' + this.name;
        }

        const last_actor = actors.pop();

        return (actors.isNotEmpty() ? actors.join(', ') + ' and ' : ' ') + last_actor + ' can ' + this.name;
    }

    get tasks_estimate() {
        return this.tasks.reduce((total, task) => total + task.estimate, 0);
    }

    get is_filtered() {
        const filters = this.feature.project.filters;

        if (typeof filters.statuses.completed === 'boolean' && this.is_complete !== filters.statuses.completed) {
            return true;
        }

        if (typeof filters.statuses.blocked === 'boolean' && this.is_blocked !== filters.statuses.blocked) {
            return true;
        }

        if (typeof filters.statuses.estimated === 'boolean' && this.tasks_estimate > 0 !== filters.statuses.estimated) {
            return true;
        }

        if (typeof filters.statuses.has_tasks === 'boolean' && this.tasks.isNotEmpty() !== filters.statuses.has_tasks) {
            return true;
        }

        if (typeof filters.statuses.has_unknowns === 'boolean' && this.unknowns.isNotEmpty() !== filters.statuses.has_unknowns) {
            return true;
        }

        if (filters.has_actors) {
            const actor_ids = this.assignments.pluck('actor_id');

            if (!Object.entries(filters.actors).every(([id, required]) => required ? actor_ids.contains(id) : actor_ids.doesntContain(id))) {
                return true;
            }
        }

        return false;
    }

    onDelete() {
        const comment_ids = this.comments.pluck('id').toArray();

        this.constructor.repository('comments').deleteMany(comment_ids);
    }
}
