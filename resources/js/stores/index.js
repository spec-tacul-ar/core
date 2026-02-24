import alerts from './modules/alerts';
import Assignment from './models/Assignment';
import Feature from './models/Feature';
import modal from './modules/modal';
import Model from './Model';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import Project from './models/Project';
import Repository from './Repository';
import Requirement from './models/Requirement';
import Task from './models/Task';
import Unknown from './models/Unknown';
import User from './models/User';
import { createPinia, defineStore } from 'pinia';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

export const useAlertsStore = defineStore('alerts', alerts);
export const useModalStore = defineStore('modal', modal);

const defaults = {
    assignments: defineStore('assignments', Repository(Assignment)),
    features: defineStore('features', Repository(Feature)),
    projects: defineStore('projects', Repository(Project)),
    unknowns: defineStore('unknowns', Repository(Unknown)),
    requirements: defineStore('requirements', Repository(Requirement)),
    tasks: defineStore('tasks', Repository(Task)),
    users: defineStore('users', Repository(User)),
};

export default {
    install(app, overrides = {}) {
        const repositories = { ...defaults, ...overrides };

        Model.repositories = repositories;

        app.use(pinia);
    },
};
