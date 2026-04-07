<p align="center"><a href="https://spec.tacul.ar" target="_blank"><img src="https://raw.githubusercontent.com/syntheticminds/spectacular/master/public/images/logo.svg" width="400" alt="Spectacular Logo"></a></p>

## About Spectacular

Spectacular is a free open-source functional specification builder.

Turn messy project scope into a build-ready single source of truth everyone can understand.

* Maintain the specification as change requests come in
* Estimate effort and track progress
* Document ambiguities and highlight blockers
* Effortlessly export to Markdown and HTML

### Roadmap
* More export formats
* Dark mode for working late
* Browsable changelog (changes are already being stored!)
* Versioning, including a comparison tool
* Presence indicators and live updates
* Obligatory AI integration
* Desktop app

## Installation

> [!TIP]
> Skip setup, updates, and backups with [Spectacular Cloud](https://spec.tacul.ar).

Spectacular is installed and configured like any other Laravel application.

```bash
git clone https://github.com/syntheticminds/spectacular.git
cd spectacular
composer setup
php artisan serve
```

To upgrade, simply grab the latest code and run `composer setup` again.

### Docker

If you don't have PHP available, you can run the app with Docker. Once ready, you'll find Spectacular running at [http://localhost:8000](http://localhost:8000).

```bash
git pull
docker compose up --build
```

Upgrades are simple too - just grab the latest code and rebuild the container. The database will be preservered.

## Contributing

Thank you for considering contributing to Spectacular! Please see [CONTRIBUTING.md](https://github.com/syntheticminds/spectacular/blob/main/CONTRIBUTING.md) for more information.

## Security

If you discover a security vulnerability within Spectacular, please send an e-mail to Matthew White via [matt@matthewwhite.me.uk](mailto:matt@matthewwhite.me.uk). All security vulnerabilities will be promptly addressed.

## License

Spectacular - an open-source functional specification manager\
Copyright (C) 2026 Matthew White

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program.  If not, see <https://www.gnu.org/licenses/>.
