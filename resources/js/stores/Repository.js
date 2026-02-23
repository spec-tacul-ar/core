import collect from 'collect.js';

export default (model) => {
    return {
        actions: {
            save(object) {
                let item = this.find(object.id);

                if (item) {
                    return Object.assign(item, object);
                }

                item = new model(object);
                
                this.items.push(item);

                return item;
            },
            saveMany(objects) {
                return objects.map(object => this.save(object));
            },
            delete(id) {
                const index = this.items.findIndex(item => item.id === id);

                if (typeof this.items[index].onDelete === 'function') { 
                    this.items[index].onDelete();
                }

                if (index !== -1) {
                    this.items.splice(index, 1);
                }
            },
            deleteMany(ids) {
                ids.forEach(id => this.delete(id));
            },
        },
        getters: {
            collection: (state) => {
                return collect(state.items);
            },
            find: (state) => {
                return (id) => state.items.find(item => item.id === id);
            },
        },
        state: () => {
            return {
                'items': []
            };
        }
    };
};
