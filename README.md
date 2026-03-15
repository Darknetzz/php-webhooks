# PHP Webhooks

A simple self-hosted app to create, edit, and delete webhooks and view requests sent to them.

## Features

- **Create / edit / delete webhooks** (admin login required)
- **View requests** made to each webhook (method, headers, body, query string, IP, time)
- **Public listing**: optional “public” flag so anyone can see the webhook URL; otherwise only the owner sees it in their dashboard
- **SQLite** by default; **MySQL** supported via config
- **Onboarding**: when no users exist, the first visitor sees a setup page to create the owner (superadmin) account

## Requirements

- PHP 8.0+ with PDO (SQLite or MySQL), **or** Docker

## Installation

### Docker (recommended):

**Option 1: Docker Compose**

The image uses Apache with document root set to `public/`, so routes like `/login` work without extra server config. Ideal behind a reverse proxy (e.g. Nginx Proxy Manager): point the proxy at the container (host:port or service name), no path needed.

1. Clone the repo and create `.env` from the example:
   ```bash
   cp .env.example .env
   ```
2. Edit `.env`: set `APP_URL` to your public URL (e.g. `https://webhooks.example.com`).
3. Run with Docker Compose:
   ```bash
   docker compose up -d
   ```

```yml
# Stack-friendly: no .env file required. Set environment variables in your
# platform (Portainer, etc.) or below. Use this when the stack project dir
# has no .env (e.g. "env file .env not found").
#
# Portainer: add variables in the stack editor under "Environment variables",
# or paste this and set APP_URL in the environment section.

services:
  webhooks:
    image: darknetz/php-webhooks:latest
    ports:
      - "8567:80"
    environment:
      APP_SECRET: ${APP_SECRET}
      APP_ENV: ${APP_ENV:-production}
      APP_DEBUG: ${APP_DEBUG:-0}
      APP_URL: ${APP_URL:-http://localhost/webhooks/public}
      # APP_URL_PUBLIC: ${APP_URL_PUBLIC:-https://webhooks.example.com}
      # APP_BASE_PATH: ${APP_BASE_PATH:-webhooks/public}
      DB_DRIVER: ${DB_DRIVER:-sqlite}
      DB_PATH: ${DB_PATH:-/var/www/html/data/database.sqlite}
    volumes:
      - webhooks_data:/var/www/html/data
    restart: unless-stopped

volumes:
  webhooks_data:
```

**Option 1: Pre-built images**

Pull from Docker Hub or GitHub Container Registry instead of building locally:
   ```bash
   # Pull image
   docker pull darknetz/php-webhooks:latest # alternatively ghcr.io/Darknetzz/php-webhooks
   # Run container
   docker run -d -p 8567:80 -v "$(pwd)/.env:/var/www/html/.env:ro" -v webhooks_data:/var/www/html/data --name webhooks darknetz/php-webhooks:latest
   ```
   Image: [darknetz/php-webhooks](https://hub.docker.com/repository/docker/darknetz/php-webhooks). For ghcr.io use `ghcr.io/<owner>/<repo>:latest` when the repo publishes it.

**Option 2: Build and run**

   ```bash
   # Clone git repo
   git clone https://github.com/Darknetzz/php-webhooks.git
   cd php-webhooks

   # Configure .env
   cp .env.example .env
   # Make changes to .env

   # Build and run container
   docker build -t webhooks .
   docker run -d -p 8567:80 -v $(pwd)/.env:/var/www/html/.env:ro -v webhooks_data:/var/www/html/data --name webhooks webhooks
   ```

### Without Docker

1. Clone or copy the project to your web root or a subdirectory.
2. Copy `.env.example` to `.env` and set at least `APP_URL`.
3. **SQLite (default):** Create a writable `data/` directory; the web server user must be able to write to it (e.g. `chown www-data:www-data data`).
4. Point the web server document root to the `public` folder (see *URL rewriting* and *Document root* below).
5. Open the app in a browser; complete onboarding if needed, then create webhooks. Each webhook gets a URL like `{APP_URL}/w/{slug}`.

See [docs](docs/) for Nginx or Apache2 setup.

## Configuration

### Environment (`.env`)

Set these in a `.env` file in the project root (or pass them to the container). At minimum, set `APP_URL` to the URL you use to open the app.

**App**

| Variable | Description | Default |
|----------|--------------|---------|
| `APP_ENV` | Environment name (e.g. `production`). | `production` |
| `APP_DEBUG` | Show PHP errors in the browser: `0` or `1`. Use `1` only for debugging; use `{APP_URL}/--db-check` to see DB errors. | `0` |
| `APP_URL` | **Required.** Full base URL of the app with no trailing slash (e.g. `https://webhooks.example.com` or `http://<yourserver>/webhooks/public`). Used for login redirects, links, and webhook URLs. | `http://localhost` |
| `APP_SECRET` | Optional secret key for the app. Leave empty if not used. | — |
| `APP_BASE_PATH` | Optional. Subpath where the app is served (e.g. `webhooks/public` for `http://host/webhooks/public/`). Only set if links or redirects are wrong (e.g. behind a proxy that doesn’t set the request path correctly). Normally the app detects the path from the request. | — |
| `APP_URL_PUBLIC` | Optional. Public URL used for webhook endpoints in the UI and examples. Set when `APP_URL` is internal (e.g. `http://backend/`) but webhooks must be called at a different public URL. If unset, `APP_URL` is used. | — |

**Database (SQLite, default)**

| Variable | Description | Default |
|----------|--------------|---------|
| `DB_DRIVER` | Database driver: `sqlite` or `mysql`. | `sqlite` |
| `DB_PATH` | Path to the SQLite file. With Docker, use `/var/www/html/data/database.sqlite` so the volume is used. | `data/database.sqlite` |

**Database (MySQL, when `DB_DRIVER=mysql`)**

| Variable | Description | Default |
|----------|--------------|---------|
| `DB_HOST` | MySQL host. | `127.0.0.1` |
| `DB_PORT` | MySQL port. | `3306` |
| `DB_NAME` | Database name. | `webhooks` |
| `DB_USER` | MySQL user. | — |
| `DB_PASSWORD` | MySQL password. | — |
| `DB_CHARSET` | Connection charset. | `utf8mb4` |

**Build/push script only** (not used by the app at runtime)

Used by `scripts/docker-build-push.sh` when building and pushing the image from your machine:

| Variable | Description |
|----------|-------------|
| `DOCKERHUB_USERNAME` | Docker Hub username for `docker login`. |
| `DOCKERHUB_TOKEN` | Docker Hub access token (or password). Prefer a token over a password. |
| `DOCKER_IMAGE` | Override image name (default: `darknetz/php-webhooks`). |
| `GHCR_IMAGE` | If set (e.g. `ghcr.io/owner/repo`), the script also pushes to GitHub Container Registry. |

### Proxy and direct access

- **Behind a reverse proxy** (e.g. Nginx Proxy Manager): Set `APP_URL` to the public URL (e.g. `https://webhooks.roste.org`). The proxy should send `X-Forwarded-Host` and `X-Forwarded-Proto` so the app uses that URL for links and redirects.
- **Direct access** (e.g. `http://<yourserver>/webhooks/public/`): You can use the app without setting `APP_URL`, or set it to the direct URL. Links and redirects are derived from the current request, so you stay on the same base URL you used to open the app.

### App at a subpath (without Docker)

If you run the app **without Docker** and the proxy forwards to a path on the backend (e.g. `http://backend/webhooks/public/`), or you access it at `http://<yourserver>/webhooks/public/`, the backend must route that path to `public/index.php`. With **Docker**, the container serves from `/`; point the proxy at the container with no path.

- **Apache** (doc root e.g. `/var/www/html`): Only if the app at `/webhooks/public` is not yet served — add an Alias and Directory; `public/.htaccess` does the rewrite. **Optional** example: [docs/apache-subpath.md](docs/apache-subpath.md).
- **Nginx**: add the `location` blocks from [docs/nginx-subpath.md](docs/nginx-subpath.md) inside your default `server { }` and set the correct `fastcgi_pass`.

After that, direct `http://<yourserver>/webhooks/public/login` and the proxy will work.

### URL rewriting

- **Apache**: Use the provided `public/.htaccess` and ensure `mod_rewrite` is enabled.
- **Nginx**: Route all non-file requests to `public/index.php`:
  ```nginx
  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }
  ```
- **PHP built-in server** (development):
  ```bash
  cd /path/to/php-webhook
  php -S localhost:8000 -t public public/router.php
  ```
  Then set `APP_URL=http://localhost:8000` in `.env`.

### Document root must be `public/` (without Docker)

**With Docker**, the image already uses `public/` as document root. **Without Docker**, if `/login` or other routes show the wrong page, the document root must point at the **`public`** directory, not the project root.

- **Apache** – set `DocumentRoot` to the full path to `public`:
  ```apache
  DocumentRoot /var/www/html/webhooks/public
  <Directory /var/www/html/webhooks/public>
      AllowOverride All
      Require all granted
  </Directory>
  ```
- **Nginx** – set `root` to the full path to `public`:
  ```nginx
  root /var/www/html/webhooks/public;
  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }
  ```

## Usage

- **Receive webhooks**: Any HTTP request to `{APP_URL}/w/{slug}` is logged (method, headers, body, query string, IP) and answered with `200` and `{"ok":true,"received":true}`. No auth required to send; only creating/editing/deleting webhooks and viewing request logs requires admin login.
- **Public vs private**: When creating/editing a webhook, “List on public page” controls whether it appears on the home page for unauthenticated users. The endpoint always accepts requests; this only affects visibility of the URL on the site.
- **View requests**: Log in → My Webhooks → “View requests” on a webhook to see all received requests and expand details (headers, body, etc.).

## Troubleshooting

**`/login` or other routes show the server’s root index.php:** The request never reaches this app. The backend must route `/webhooks/public/*` to the app (Apache: [docs/apache-subpath.md](docs/apache-subpath.md); Nginx: [docs/nginx-subpath.md](docs/nginx-subpath.md)). See “App at a subpath” above.

**"env file .env not found" (stack deploy):** Use `docker-compose.stack.yml` or create `.env` in the stack directory. See "Stack deploy" under Docker.

**"Database error" in the browser:** To see the actual error (e.g. permissions, path, or MySQL connection), either:

1. Set `APP_DEBUG=1` in `.env`, then open `{APP_URL}/--db-check` in the browser. You’ll get a plain-text message with the real error. Set `APP_DEBUG=0` again afterwards.
2. Check the server’s PHP error log for lines starting with `Webhooks DB error:`.

**CLI check:** From the project root run `php check-db.php` (or `sudo -u www-data php check-db.php` to test as the web server user).

## Project structure

```
config/
  config.php      # Config array (reads from env)
  bootstrap.php   # Load .env and helpers
src/
  Database.php    # PDO factory (SQLite/MySQL), migrations
  Auth.php        # Session-based auth
  User.php, UserRepository.php
  Webhook.php, WebhookRepository.php
  WebhookRequest.php, WebhookRequestRepository.php
  helpers.php     # config(), db(), auth(), e(), redirect()
public/
  index.php       # Front controller (routes, onboarding, admin, home)
  receive_webhook.php  # Handles /w/{slug}
  assets/style.css
  .htaccess
templates/        # PHP templates
data/             # SQLite DB (created automatically, gitignored)
```

## Development and release

### Branch model

- **`main`** – stable release branch. Write-protected; updates only via pull requests from `dev`. The Docker image tag `latest` is built from `main` after each release merge.
- **`dev`** – integration branch for day-to-day work; set as the repo **default branch** so new clones and pull requests target it. All feature work and changelog edits happen here.

The `dev` branch is created from `main` and pushed once (already done for this repo).

**Branch protection:** `main` has a protection rule (e.g. require a pull request before merging). Configure or adjust it in **GitHub → Settings → Branches**.

### Changelog

[CHANGELOG.md](CHANGELOG.md) follows [Keep a Changelog](https://keepachangelog.com/). Keep the **\[Unreleased\]** section updated with changes as you work on `dev`. The release workflow turns that section into a versioned entry when you cut a release.

### Releasing a new version

1. **Run the Release workflow**  
   In GitHub: **Actions → Release → Run workflow**. Enter the version (e.g. `1.1.0`, no `v` prefix). The workflow will:
   - Update CHANGELOG.md on `dev` (replace `[Unreleased]` with the new version and date, add a new `[Unreleased]`).
   - Commit and push to `dev`.
   - Open a pull request **dev → main**.
   - Create tag `vX.Y.Z` and publish a GitHub Release (which triggers the Docker build and push).

2. **Merge the release PR**  
   Merge the created PR (dev → main) in the GitHub UI. That syncs `main` with the release. The Docker image `:latest` is updated when a release is published (CI does not push on every merge to main).

3. **Optional local script**  
   You can run `./scripts/release.sh` interactively (shows last version, asks for next, summarizes changelog, then commits and pushes the tag), or `./scripts/release.sh <version> [YYYY-MM-DD]` to update CHANGELOG only (e.g. for CI); the workflow uses the non-interactive form. When running the script interactively, you can choose to update the main branch (merge dev into main) so you don't have to open a PR manually. If `main` is protected, the script will try to create and merge a PR via the GitHub CLI (`gh`), or you can merge the PR yourself.

## Publishing the image

### Local pre-push hook (no GitHub Actions)

Build and push the image from your machine when you push `main`. Uses your existing `docker login`.

1. **One-time:** log in to Docker Hub (and ghcr.io if you use it):
   ```bash
   docker login
   docker login ghcr.io   # optional, for GitHub Container Registry
   ```
2. **Install the hook:**
   ```bash
   cp scripts/pre-push.sample .git/hooks/pre-push && chmod +x .git/hooks/pre-push
   ```
3. Optional: install the pre-push hook so that when you push to `main`, `scripts/docker-build-push.sh` runs and pushes `darknetz/php-webhooks:latest` (and `:tag` if the commit is tagged). To also push to ghcr.io, set `GHCR_IMAGE=ghcr.io/owner/repo` in your environment. CI pushes images on **release published** and on **push to dev** (not on every push to main), to avoid accumulating many untagged digests.

You can also run the script manually: `./scripts/docker-build-push.sh`.

### GitHub Actions (optional)

The repo also includes `.github/workflows/docker-publish.yml` if you prefer CI to build and push on push/release. See the workflow file and repo Settings → Secrets for setup.

## License

MIT.
