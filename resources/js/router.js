import AccountDelete from '@/components/pages/AccountDelete.vue';
import AccountSettings from '@/components/pages/AccountSettings.vue';
import FeatureForm from '@/components/sidepanels/Feature.vue';
import Login from '@/components/pages/Login.vue';
import Model from '@/stores/Model';
import NotFound from '@/components/pages/NotFound.vue';
import PasswordRequest from '@/components/pages/PasswordRequest.vue';
import PasswordReset from '@/components/pages/PasswordReset.vue';
import ProjectCreate from '@/components/pages/ProjectCreate.vue';
import ProjectEdit from '@/components/sidepanels/ProjectEdit.vue';
import ProjectFeedback from '@/components/sidepanels/ProjectFeedback.vue';
import ProjectOrganise from '@/components/sidepanels/ProjectOrganise.vue';
import ProjectPeople from '@/components/sidepanels/ProjectPeople.vue';
import ProjectShow from '@/components/pages/ProjectShow.vue';
import ProjectsIndex from '@/components/pages/ProjectsIndex.vue';
import Register from '@/components/pages/Register.vue';
import RequirementForm from '@/components/sidepanels/Requirement.vue';
import ActorForm from '@/components/sidepanels/Actor.vue';
import { Api } from '@/api';
import { createRouter, createWebHistory } from 'vue-router';
import { useAlertsStore, useAuthStore } from '@/stores';

function buildRouter(base) {
    const api = new Api();

    if (!base) {
        base = window.Spectacular.path + '/';
    }

    const router = createRouter({
        history: createWebHistory(base),
        scrollBehavior(to, from, saved_position) { // TODO Look at this function again. It can be tidied.
            if (to.hash) {
                return; // We handle this in the afterEach() callback.
            }

            const has_sidepanel = Object.hasOwn(to.matched.at(-1).components, 'sidepanel');

            if (has_sidepanel) {
                return;
            }

            if (from.name) {
                const had_sidepanel = Object.hasOwn(from.matched.at(-1).components, 'sidepanel');

                if (had_sidepanel) {
                    return;
                }
            }

            if (saved_position) {
                return {...saved_position, behavior: 'instant'};
            }

            return { top: 0, behavior: 'instant' };
        },
        routes: [
            // Auth
            {
                name: 'auth.login',
                path: '/login',
                component: Login,
                meta: { solo: false, auth: 'guest', title: 'Log in' },
            }, {
                name: 'auth.register',
                path: '/register',
                component: Register,
                meta: { solo: false, auth: 'guest', title: 'Register' },
            }, {
                name: 'auth.password.request',
                path: '/password/request',
                component: PasswordRequest,
                meta: { solo: false, auth: 'guest', title: 'Request password reset' },
            }, {
                name: 'auth.password.reset',
                path: '/password/reset/:token',
                component: PasswordReset,
                meta: { solo: false, auth: 'guest', title: 'Reset password reset' },
                props: true,
            }, {
                name: 'account.settings',
                path: '/account/settings',
                component: AccountSettings,
                meta: { solo: false, title: 'Account settings' },
            }, {
                name: 'account.delete',
                path: '/account/delete',
                component: AccountDelete,
                meta: { solo: false, title: 'Delete account' },
            },

            // Projects
            {
                name: 'home',
                path: '/',
                redirect: { name: 'projects.index' },
            }, {
                name: 'projects.index',
                path: '/projects',
                component: ProjectsIndex,
                meta: { title: 'Projects' },
            },
            {
                name: 'projects.create',
                path: '/projects/create',
                meta: { title: 'Create project' },
                component: ProjectCreate,
            }, {
                name: 'projects.show',
                path: '/projects/:project_id',
                component: ProjectShow,
                props: true,
                children: [
                    {
                        name: 'projects.edit',
                        path: 'edit',
                        meta: { title: 'Edit project' },
                        components: { sidepanel: ProjectEdit },
                        props: true,
                    }, {
                        name: 'projects.organise',
                        path: 'organise',
                        meta: { title: 'Organise project' },
                        components: { sidepanel: ProjectOrganise },
                        props: true,
                    }, {
                        name: 'projects.feedback',
                        path: 'feedback',
                        meta: { solo: false, title: 'Feedback' },
                        components: { sidepanel: ProjectFeedback },
                        props: true,
                    }, {
                        name: 'projects.people',
                        path: 'people',
                        meta: { solo: false, title: 'People' },
                        components: { sidepanel: ProjectPeople },
                        props: true,
                    },

                    // Users
                    {
                        name: 'projects.actors.create',
                        path: 'actors/create',
                        meta: { title: 'Create user' },
                        components: { sidepanel: ActorForm },
                        props: true,
                    }, {
                        name: 'projects.actors.edit',
                        path: 'actors/:actor_id/edit',
                        meta: { title: 'Edit user' },
                        components: { sidepanel: ActorForm },
                        props: true,
                    },

                    // Features
                    {
                        name: 'projects.features.create',
                        path: 'features/create',
                        meta: { title: 'Create feature' },
                        components: { sidepanel: FeatureForm },
                        props: true,
                    }, {
                        name: 'projects.features.edit',
                        path: 'features/:feature_id/edit',
                        meta: { title: 'Edit feature' },
                        components: { sidepanel: FeatureForm },
                        props: true,
                    }, {
                        name: 'projects.features.requirements.create',
                        path: 'features/:feature_id/requirements/create',
                        meta: { title: 'Create requirement' },
                        components: { sidepanel: RequirementForm },
                        props: true,
                    }, {
                        name: 'projects.features.feedback',
                        path: 'features/:feature_id/feedback',
                        meta: { title: 'Feedback' },
                        components: { sidepanel: ProjectFeedback },
                        props: true,
                    },

                    // Requirements
                    {
                        name: 'projects.requirements.create',
                        path: 'requirements/create',
                        meta: { title: 'Create requirement' },
                        components: { sidepanel: RequirementForm },
                        props: true,
                    }, {
                        name: 'projects.requirements.edit',
                        path: 'requirements/:requirement_id/edit',
                        meta: { title: 'Edit requirement' },
                        components: { sidepanel: RequirementForm },
                        props: true,
                    }, {
                        name: 'projects.requirements.feedback',
                        path: 'requirements/:requirement_id/feedback',
                        meta: { title: 'Feedback' },
                        components: { sidepanel: ProjectFeedback },
                        props: true,
                    },
                ],
            },

            // Miscellaneous
            {
                name: '404',
                path: '/:pathMatch(.*)*',
                component: NotFound,
                meta: { title: 'Not found' },
            },
        ],
    });

    // Remove the splash when we're ready.
    router.isReady().then(() => {
        if (document.getElementById('splash')) {
            document.getElementById('splash').remove();
            document.getElementById('app').classList.remove('hide');
        }
    });

    // Add some auth middleware
    router.beforeEach(async (to) => {
        const auth_store = useAuthStore();
        const was_logged_in = auth_store.is_logged_in;

        // See if we can load the user's account
        if (!auth_store.account) {
            await api.get('auth/account')
                .then(response => {
                    auth_store.setAccount(response.data);

                    if (!was_logged_in && auth_store.is_logged_in) {
                        Model.resetRepositories();
                    }
                })
                .catch((error) => {
                    if (error.response.status !== 401) {
                        throw error;
                    }
                });
        }

        // Redirect authenticated users
        if (auth_store.is_logged_in && to.meta.auth === 'guest') {
            return {'name': 'projects.index'};
        }

        // Redirect guests
        if (!to.meta.auth && !auth_store.is_logged_in) {
            return {'name': 'auth.login'};
        }

        // Disable authentication and collaboration routes for solo users
        if (auth_store.is_logged_in && window.Spectacular.mode === 'solo' && to.meta.solo === false) {
            useAlertsStore().push('Not available in solo mode.', 'warning');

            return { name: 'home' };
        }
    });

    // Clear the account if we're not logged in
    router.afterEach(() => {
        const auth_store = useAuthStore();

        if (!auth_store.is_logged_in) {
            auth_store.account = null;
        }
    });

    // Scroll hashes smoothly.
    router.afterEach(to => {
        if (!to.hash) {
            return;
        }

        // We can't use nextTick() because something else happens before mount.
        setTimeout(() => {
            const element = document.getElementById(to.hash.substring(1));

            if (element) {
                element.scrollIntoView();
            }
        }, 100);
    });

    return router;
}

export default {
    install(app, options = {}) {
        let base = null;
        let callback = null;

        if (typeof options === 'string') {
            base = options;
        } else if (typeof options === 'function') {
            callback = options;
        } else if (options && typeof options === 'object') {
            base = options.base;
            callback = options.callback;
        }

        const router = buildRouter(base);

        if (callback) {
            callback(router, app);
        }

        app.use(router);
    },
};
