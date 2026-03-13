# PHP Webhooks

A simple self-hosted app to create, edit, and delete webhooks and view requests sent to them.

## Features

- **Create / edit / delete webhooks** (admin login required)
- **View requests** made to each webhook (method, headers, body, query string, IP, time)
- **Public listing**: optional “public” flag so anyone can see the webhook URL; otherwise only the owner sees it in their dashboard
- **SQLite** by default; **MySQL** supported via config
- **Onboarding**: when no users exist, the first visitor sees a setup page to create the owner (superadmin) account

## Requirements

- PHP 8.0+
- PDO with SQLite (default) or MySQL

## Installation

1. Clone or copy the project to your web root or a subdirectory.
2. Copy `.env.example` to `.env` and set at least:
   - `APP_URL` – full URL to the app (e.g. `http://localhost` or `https://yourdomain.com/webhooks`)
   - `APP_SECRET` (optional, for future use)
3. **SQLite (default):** Ensure a writable `data/` directory exists (e.g. `mkdir -p data`). The web server user (e.g. `www-data`) must be able to write to it. If the project is already deployed as that user, no `chown` is needed; otherwise run `chown www-data:www-data data` (requires root/sudo).
4. Point your web server document root to the `public` folder (or run the built-in server from the project root, see below).
5. Open the app in a browser. If no users exist, you’ll get the onboarding page to create the first owner account.
6. Log in and create webhooks under **My Webhooks**. Each webhook gets a URL like:
   `{APP_URL}/w/{slug}`

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

## Usage

- **Receive webhooks**: Any HTTP request to `{APP_URL}/w/{slug}` is logged (method, headers, body, query string, IP) and answered with `200` and `{"ok":true,"received":true}`. No auth required to send; only creating/editing/deleting webhooks and viewing request logs requires admin login.
- **Public vs private**: When creating/editing a webhook, “List on public page” controls whether it appears on the home page for unauthenticated users. The endpoint always accepts requests; this only affects visibility of the URL on the site.
- **View requests**: Log in → My Webhooks → “View requests” on a webhook to see all received requests and expand details (headers, body, etc.).

## Troubleshooting

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

## License

MIT.
