import { alphanumeric } from 'nanoid-dictionary';
import { customAlphabet } from 'nanoid/non-secure';

const nanoid = customAlphabet(alphanumeric, 5);

export default {
    data: function () {
        return {
            component_id: nanoid()
        };
    },
    methods: {
        elementId: function (field_name) {
            return field_name + '_' + this.component_id;
        }
    }
};
