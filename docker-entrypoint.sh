#!/bin/sh
# Log release/commit version to stdout (shows in docker logs) then start Apache
v="$(cat /var/www/html/version.txt 2>/dev/null)" || v="unknown"
echo "php-webhooks version: ${v}"
exec "$@"
