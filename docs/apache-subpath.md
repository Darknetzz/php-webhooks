# Apache: app at a subpath (optional)

**You do not need this if:**

- You use Docker (the container serves from `/`).
- Your document root is already the app's `public/` folder.
- Your server already routes `/webhooks/public` to the app (e.g. via another conf or vhost).

Use this only when the app lives under a subpath and requests to that path are not yet reaching `public/index.php`.

## Steps

1. Copy the config below to `/etc/apache2/conf-available/webhooks.conf`.
2. Adjust the paths if the app is not under `/var/www/html/webhooks`.
3. Run: `sudo a2enconf webhooks && sudo systemctl reload apache2`.

## Config

```apache
Alias /webhooks/public /var/www/html/webhooks/public
<Directory /var/www/html/webhooks/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

The app's `public/.htaccess` (no RewriteBase) handles the rewrite to `index.php`.
