import Model from '../Model';

export default class Unknown extends Model {
    get requirement() {
        return this.constructor.repository('requirements').find(this.requirement_id);
    }
}
