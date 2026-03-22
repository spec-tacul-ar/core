import alerts from './modules/alerts';
import auth from './modules/auth';
import Assignment from './models/Assignment';
import Comment from './models/Comment';
import Contributor from './models/Contributor';
import Feature from './models/Feature';
import Invitation from './models/Invitation';
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
export const useAuthStore = defineStore('auth', auth);
export const useModalStore = defineStore('modal', modal);

Model.repositories = {
    assignments: defineStore('assignments', Repository(Assignment)),
    comments: defineStore('comments', Repository(Comment)),
    contributors: defineStore('contributors', Repository(Contributor)),
    features: defineStore('features', Repository(Feature)),
    invitations: defineStore('invitations', Repository(Invitation)),
    projects: defineStore('projects', Repository(Project)),
    requirements: defineStore('requirements', Repository(Requirement)),
    tasks: defineStore('tasks', Repository(Task)),
    unknowns: defineStore('unknowns', Repository(Unknown)),
    users: defineStore('users', Repository(User)),
};

export default pinia;
