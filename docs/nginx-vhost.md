# Nginx: dedicated vhost (document root = public/)

Use this when the app has its own server block (e.g. `webhooks.example.com`). The root must be the app's `public/` directory so `/login` and other routes work.

Adjust `server_name` and paths as needed.

## Config

```nginx
server {
    listen 80;
    server_name webhooks.example.com;
    root /var/www/html/webhooks/public;

    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;  # or 127.0.0.1:9000
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
