#!/bin/sh
# Development helper: copies the overlay into the running demo container without rebuilding.
set -eu
cd "$(dirname "$0")"
APP=$(docker compose ps -q app)
docker cp ../views/. "$APP":/var/www/html/app/views/
docker exec "$APP" sh -c 'if [ -f /var/www/html/app/views/upstream/navbar.php ]; then cp /var/www/html/app/views/layout/*.php /var/www/html/app/views/; fi'
docker cp ../public/css/. "$APP":/var/www/html/public/css/
docker cp ../public/js/. "$APP":/var/www/html/public/js/
docker cp ../public/fonts/. "$APP":/var/www/html/public/fonts/
