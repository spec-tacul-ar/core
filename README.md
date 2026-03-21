<p align="center"><a href="https://spec.tacul.ar" target="_blank"><img src="https://raw.githubusercontent.com/syntheticminds/spectacular/master/public/images/logo.svg" width="400" alt="Spectacular Logo"></a></p>

## About Spectacular

Spectacular is a free open-source functional specification manager.

Turn messy project scope into build-ready specifications with explicit blockers, estimates, and exportable handoff docs.

* Build detailed specfications
* Maintain them as requirements change
* Estimate effort and track progress
* Document ambiguities and highlight blockers
* Export to Markdown and HTML effortlessly

### Features in the works...
* More export formats
* Dark mode for working late
* Browsable changelog (changes are already being stored!)
* Versioning, including a comparison tool
* Obligatory AI integration

## Installation

> [!TIP]
> Skip setup, updates, and backups with [Spectacular Cloud](https://spec.tacul.ar) - the hosted version of Spectacular for teams that want to start writing specs immediately.

Spectacular installs like any other Laravel application.

```bash
git clone https://github.com/syntheticminds/spectacular.git
cd spectacular
composer setup
# Set your APP_URL in the .env file
```

To upgrade, simply grab the latest code and run `composer setup` again.

### Docker

If you don't have PHP available, you can run the app with Docker. Once ready, you'll find Spectacular running at [http://localhost:8000](http://localhost:8000).

```bash
git pull
docker compose up --build
```

Upgrades are simple too - just grab the latest code and rebuild the container. The database will be preservered.

## Extending

Spectacular is designed to be forked and extended for your purposes. Of couse, if you've made something cool that other might use, please consider contributing it back to the project.

### Laravel

API endpoints are registered as actions in `app/Actions`. You can either use the service container to [change the binding](https://laravel.com/docs/12.x/container#binding-interfaces-to-implementations) to your own custom implementation or by defining your own routes in `routes/api.php`.

### Vue

To override Vue templates, there are a number of global components that you can override simply by adding them to `resources/js/app.js`. They will be merged with the defaults.


```js
import ProjectShowMenu from 'path/to/my/ProjectShowMenu.vue';
import UserMenu from 'path/to/my/UserMenu.vue';

const app = createApp(App)
    .use(api)
    .use(components, {
        ProjectShowMenu,
        UserMenu,
    }
```

Need more extension hooks? Open a pull request.

## Contributing

Thank you for considering contributing to Spectacular! Please see CONTRIBUTING.md for more information.

## Security

If you discover a security vulnerability within Spectacular, please send an e-mail to Matthew White via [matt@matthewwhite.me.uk](mailto:matt@matthewwhite.me.uk). All security vulnerabilities will be promptly addressed.

## License

Spectacular - an open-source functional specification manager\
Copyright (C) 2026 Matthew White

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program.  If not, see <https://www.gnu.org/licenses/>.
