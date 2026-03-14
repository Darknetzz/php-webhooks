# Nginx: app at a subpath

Use this when the app is served at a subpath (e.g. `http://yourserver/webhooks/public/`). Include the `location` blocks inside your default `server { }` and set the correct `fastcgi_pass` (PHP-FPM socket or upstream).

Adjust the paths if the app is not under `/var/www/html/webhooks`.

## Config

```nginx
location /webhooks/public {
    alias /var/www/html/webhooks/public;
    index index.php;
    try_files $uri $uri/ @webhooks_front;
}
location @webhooks_front {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /var/www/html/webhooks/public/index.php;
    fastcgi_param SCRIPT_NAME /webhooks/public/index.php;
    fastcgi_param REQUEST_URI $request_uri;
    fastcgi_pass unix:/run/php/php-fpm.sock;   # or 127.0.0.1:9000
}
```
