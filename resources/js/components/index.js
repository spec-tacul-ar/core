import Announcements from '@core/components/Announcements.vue';
import FeatureToolbar from '@core/components/navigation/FeatureToolbar.vue';
import ProjectShowMenu from '@core/components/navigation/ProjectShowMenu.vue';
import ProjectsIndexActions from '@core/components/navigation/ProjectsIndexActions.vue';
import RequirementToolbar from '@core/components/navigation/RequirementToolbar.vue';
import SidebarSwitches from '@core/components/navigation/SidebarSwitches.vue';
import UserMenu from '@core/components/navigation/UserMenu.vue';

const defaults = {
    Announcements,
    FeatureToolbar,
    RequirementToolbar,
    ProjectShowMenu,
    ProjectsIndexActions,
    SidebarSwitches,
    UserMenu,
};

export default {
    install(app, overrides = {}) {
        const components = { ...defaults, ...overrides };

        Object.entries(components).forEach(([name, component]) => {
            app.component(name, component);
        });
    },
};
