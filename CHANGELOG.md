# Changelog

## v2.0.0 - 2026-06-10

Spectacular 2.0.0 is a major release focused on making specifications directly usable by AI coding agents while strengthening the account, collaboration, authentication, and API foundations.

### Highlights

- Added a built-in, read-only MCP server at `/mcp/specifications` so AI coding agents can list specifications, fetch a full project, inspect individual items, and retrieve changes since a timestamp.
- Added OAuth authorization for remote MCP clients, with account-managed bearer tokens for clients that need manual `Authorization: Bearer` authentication.
- Added an Account integrations page for viewing active OAuth tokens, creating bearer tokens, and revoking access.
- Added AI-assisted development documentation, including setup guides for Codex, Cursor, Claude, and generic MCP usage.
- Added Socialite provider support for Google, GitHub, and LinkedIn OpenID.
- Added email verification and self-hosting controls for registration and verification.
- Added project, feature, and requirement activity tracking so integrations can efficiently detect changed specifications.
- Added requirement actions for blocking and unblocking requirements, appending notes, toggling tasks, and marking every task in a requirement complete.
- Added `spectacular:solo:migrate` for assigning legacy solo projects to an account.
- Added `spectacular:oauth` to prepare Passport keys and personal access clients during setup.

### Changed

- Repositioned Spectacular as an AI-assisted specification workspace, with the README and docs now centered on MCP workflows and agent setup.
- Replaced Sanctum personal access tokens with Laravel Passport/OAuth for API and MCP authentication.
- Renamed contributors to collaborations and moved per-account read state onto collaborations.
- Removed estimate fields from projects and tasks. Existing task estimates are preserved during migration by appending them to task names.
- Simplified progress reporting around task completion, unknowns, blockers, collaboration counts, and archived projects.
- Normalized API action names and routes around create, index, show, edit, and focused partial-update actions.
- Moved project exports to dedicated `/exports/{project}/{type}` routes protected by project authorization.
- Updated frontend structure under `resources/js/app` and refreshed dependencies, including the OAuth/MCP packages needed for v2.

### Fixed

- Hardened nested API validation so task and unknown IDs cannot be reused across requirements or projects.
- Prevented feature edits from moving a feature into another project.
- Tightened authorization around archived projects, project exports, collaborations, assignments, and token revocation.
- Improved the estimate-removal migration so existing task estimates are retained in task names instead of being silently lost.

### Upgrade Notes

- Existing self-hosted solo installs should create or register the account that will own their projects, then run `php artisan spectacular:solo:migrate your-email@example.com`.
- `composer setup` now runs `spectacular:oauth`; deploys that do not use `composer setup` should run `php artisan spectacular:oauth` after migrating.
- API and MCP access now requires Passport authentication and a verified account unless verification is disabled with `SPECTACULAR_ENABLE_VERIFICATION=false`.
- The old solo-mode and configurable app-path settings have been removed. The app now lives under `/app`.
- Existing integrations should review API route changes, export URL changes, the contributor-to-collaboration rename, and the removal of task estimate fields.
