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

### Docker (recommended)

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
4. Open the app in a browser (e.g. `http://localhost:8567` or your proxy URL). If no users exist, you'll get the onboarding page. Log in and create webhooks under **My Webhooks**; each gets a URL like `{APP_URL}/w/{slug}`.

**Docker Compose example** (from the repo root; the repo includes `docker-compose.yml`):
   ```yaml
   services:
     webhooks:
       image: darknetz/php-webhooks:latest   # or build: . to build from source
       ports:
         - "8080:80"
       env_file:
         - .env
       volumes:
         - webhooks_data:/var/www/html/data
         - ./.env:/var/www/html/.env:ro
       restart: unless-stopped

   volumes:
     webhooks_data:
   ```
   Create a `.env` with at least `APP_URL=https://your-domain.com`, then run `docker compose up -d`.

**Reverse proxy:** Forward to `http://<container>:80` (or the host port you published). Set `APP_URL` to the public URL; the proxy should send `X-Forwarded-Host` and `X-Forwarded-Proto` so links and redirects are correct.

**Pre-built images:** Pull from Docker Hub or GitHub Container Registry instead of building locally:
   ```bash
   docker pull darknetz/php-webhooks:latest
   docker run -d -p 8080:80 -v $(pwd)/.env:/var/www/html/.env:ro -v webhooks_data:/var/www/html/data --name webhooks darknetz/php-webhooks:latest
   ```
   Image: [darknetz/php-webhooks](https://hub.docker.com/repository/docker/darknetz/php-webhooks). For ghcr.io use `ghcr.io/<owner>/<repo>:latest` when the repo publishes it.

**Build and run without Compose:**
   ```bash
   docker build -t webhooks .
   docker run -d -p 8080:80 -v $(pwd)/.env:/var/www/html/.env:ro -v webhooks_data:/var/www/html/data --name webhooks webhooks
   ```

### Without Docker

1. Clone or copy the project to your web root or a subdirectory.
2. Copy `.env.example` to `.env` and set at least `APP_URL`.
3. **SQLite (default):** Create a writable `data/` directory; the web server user must be able to write to it (e.g. `chown www-data:www-data data`).
4. Point the web server document root to the `public` folder (see *URL rewriting* and *Document root* below).
5. Open the app in a browser; complete onboarding if needed, then create webhooks. Each webhook gets a URL like `{APP_URL}/w/{slug}`.

## Configuration

### Environment (`.env`)

| Variable     | Description                          | Default        |
|-------------|--------------------------------------|----------------|
| `APP_ENV`   | Environment (e.g. production)         | production     |
| `APP_DEBUG` | Show errors (0 or 1)                  | 0              |
| `APP_URL`   | Full base URL of the app              | http://localhost |
| `APP_SECRET`| Secret key (optional)                | -              |
| `DB_DRIVER` | Database: `sqlite` or `mysql`         | sqlite         |
| `DB_PATH`   | SQLite file path (when using SQLite)  | data/database.sqlite |
| `DB_HOST`   | MySQL host (when using MySQL)         | 127.0.0.1      |
| `DB_PORT`   | MySQL port                            | 3306           |
| `DB_NAME`   | MySQL database name                   | webhooks       |
| `DB_USER`   | MySQL user                            | -              |
| `DB_PASSWORD` | MySQL password                      | -              |
| `DB_CHARSET` | MySQL charset                        | utf8mb4        |

### Proxy and direct access

- **Behind a reverse proxy** (e.g. Nginx Proxy Manager): Set `APP_URL` to the public URL (e.g. `https://webhooks.roste.org`). The proxy should send `X-Forwarded-Host` and `X-Forwarded-Proto` so the app uses that URL for links and redirects.
- **Direct access** (e.g. `http://web01/webhooks/public/`): You can use the app without setting `APP_URL`, or set it to the direct URL. Links and redirects are derived from the current request, so you stay on the same base URL you used to open the app.

### App at a subpath (without Docker)

If you run the app **without Docker** and the proxy forwards to a path on the backend (e.g. `http://backend/webhooks/public/`), or you access it at `http://web01/webhooks/public/`, the backend must route that path to `public/index.php`. With **Docker**, the container serves from `/`; point the proxy at the container with no path.

- **Apache** (default vhost, doc root e.g. `/var/www/html`): use `deploy/apache-subpath.conf.example`. Copy to `/etc/apache2/conf-available/webhooks-subpath.conf`, then `sudo a2enconf webhooks-subpath` and `sudo systemctl reload apache2`. Adjust paths in the file if the app lives elsewhere.
- **Nginx**: add the `location` block from `deploy/nginx-subpath.conf.example` inside your default `server { }` and set the correct `fastcgi_pass`.

After that, direct `http://web01/webhooks/public/login` and the proxy will work.

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

**`/login` or other routes show the server’s root index.php:** The request never reaches this app. The backend must route `/webhooks/public/*` to `public/index.php`. Use the snippet in `deploy/apache-subpath.conf.example` (Apache) or `deploy/nginx-subpath.conf.example` (Nginx) in your default vhost; see “App at a subpath” above.

**“Database error” in the browser:** To see the actual error (e.g. permissions, path, or MySQL connection), either:

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
3. On every `git push` to `main`, the hook runs `scripts/docker-build-push.sh`: it builds the image and pushes `darknetz/php-webhooks:latest` (and `:tag` if the commit is tagged). To also push to ghcr.io, set `GHCR_IMAGE=ghcr.io/owner/repo` in your environment before pushing.

You can also run the script manually: `./scripts/docker-build-push.sh`.

### GitHub Actions (optional)

The repo also includes `.github/workflows/docker-publish.yml` if you prefer CI to build and push on push/release. See the workflow file and repo Settings → Secrets for setup.

## License

MIT.
