import Model from '../Model';

export default class Token extends Model {
    static repository_name = 'tokens';

    constructor(data) {
        super(data);
    }
}
