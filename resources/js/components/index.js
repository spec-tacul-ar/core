import Announcements from '@core/components/Announcements.vue';
import FeatureToolbar from '@core/components/navigation/FeatureToolbar.vue';
import ProjectToolbar from '@core/components/navigation/ProjectToolbar.vue';
import RequirementToolbar from '@core/components/navigation/RequirementToolbar.vue';
import SidebarSwitches from '@core/components/navigation/SidebarSwitches.vue';
import UserMenu from '@core/components/navigation/UserMenu.vue';

const defaults = {
    Announcements,
    FeatureToolbar,
    RequirementToolbar,
    ProjectToolbar,
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
