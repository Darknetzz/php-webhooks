# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Visibility icons**: lock icon for private webhooks and globe icon for public/listed; reusable `visibility_label` partial used in webhook list, admin webhooks, and admin all webhooks.
- **Created date icon**: calendar icon in front of “Created” date; reusable `created_date` partial for meta lines and table cells.
- **Request count icon and link**: requests (document) icon in front of “X requests”; count is a clickable link to the request log (accent color). Removed separate “View requests” button from webhook cards.
- **Requests toolbar**: Refresh and Delete all buttons moved from the URL line into a toolbar above the requests table (admin and public request pages); buttons aligned and same size.
- **Outline danger button**: Bootstrap-style `btn-outline-danger` for the “Delete all” button on the requests page (transparent + danger border/text, filled on hover).
- **Response variables hint**: collapsible section in the webhook response config showing placeholders for dynamic data in response headers and body (e.g. `{{request.body.key}}`, `{{request.headers.X-Name}}`).
- **Reusable response fields**: shared partial for response headers and body inputs used in create/edit webhook forms and modals.
- **Cleanup workflow**: GitHub Actions workflow to clean up untagged Docker package versions.

### Changed

- **Webhook cards**: meta line shows visibility icon + label, created date with calendar icon, and request count as a single link; card actions reduced to Edit and Delete.
- **Requests page layout**: back link and webhook URL block only in the top line; Refresh and Delete all in a toolbar above the table.
- **Webhook modal**: response headers and body fields now use the shared partial; simplified input structure and improved handling of custom header rows (names/values set correctly when adding rows).
- **Docker publish**: README and GitHub Actions workflow clarified—images are pushed on release and on pushes to `dev`, not on every merge to main.
- **Request templates**: admin and public request page layout and action button organization improved; consistent toolbar and button styling.

## [1.0.2] - 2026-03-15


### Added

- **Primary color settings**: admin can set a primary/brand color for the UI; presets and hex color picker with live preview.
- **Random slug generation**: option to generate a random slug when creating a webhook.
- **Requests public**: per-webhook option to allow unauthenticated viewing of request history (public request log).
- **Release script**: optional Docker image push step in release workflow.

### Changed

- **UI**: icon-only button styling for consistency; improved button accessibility and styling in admin templates.
- **Webhook modal**: custom slug input refactored for better visibility.
- **Layout**: JavaScript color management refactored for primary color theming.

## [1.0.1] - 2026-03-15

### Added

- **Release script** (`scripts/release.sh`): interactive release helper that shows last version, prompts for next tag, summarizes changelog, and optionally commits, tags, and pushes. Non-interactive form still used by GitHub Actions.

### Changed

- **Docker**: configuration and documentation updated for improved publishing workflow.
- **User management**: user self-edit restriction and enhanced admin user edit functionality.
- **UI**: role display removed from user dropdown in layout template.
- **Documentation**: README updated to clarify branch model and protection rules.

## [1.0.0] - 2025-03-15

### Added

- **Webhooks**: create, edit, and delete webhooks (login required). Each webhook has a unique slug and URL `{APP_URL}/w/{slug}`.
- **Request logging**: every request to a webhook is logged with method, headers, body, query string, client IP, and timestamp. Logged-in owners can view request history per webhook.
- **Public listing**: optional “list on public page” flag per webhook; unauthenticated users can see public webhook URLs on the home page. Endpoints accept requests without auth; only management and viewing logs require login.
- **Custom responses**: per-webhook HTTP status code, response headers (JSON), and response body. Variable substitution in body and headers: `{{request.body.key}}`, `{{request.headers.X-Name}}`, `{{request.method}}`, `{{request.query.x}}`, `{{request.ip}}`.
- **Allowed methods**: restrict which HTTP methods each webhook accepts (e.g. POST only); returns 405 with `Allow` header for disallowed methods.
- **Database**: SQLite by default; MySQL supported via config. Automatic migrations on first run.
- **Authentication**: session-based login and logout. Optional user registration (admin-configurable).
- **Onboarding**: when no users exist, the first visitor sees a setup page to create the owner (superadmin) account.
- **User roles and admin**: superadmin and regular users; admin panel to manage users and edit any webhook. Per-user webhook list (dashboard “My Webhooks”).
- **Site settings** (admin): site name, allow registration, max webhooks per user, webhook testing enabled, allow custom test URL, test request timeout.
- **In-browser webhook testing**: send a test request from the UI with optional method, URL, headers, and body; view status and response. Optional variable substitution in test payload.
- **Profile and settings**: user profile and app settings page (e.g. site name in UI).
- **Deployment**: Docker image (Apache, document root `public/`) with Dockerfile, docker-compose, and docker-entrypoint. Pre-built images on Docker Hub and GitHub Container Registry. Docs for Nginx and Apache (vhost and subpath).
- **Configuration**: `.env`-based config (APP_URL, APP_DEBUG, APP_SECRET, APP_BASE_PATH, APP_URL_PUBLIC, DB_DRIVER, DB_PATH / MySQL options). Optional `--db-check` endpoint to diagnose database errors when APP_DEBUG is enabled.
