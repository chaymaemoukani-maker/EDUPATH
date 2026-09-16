#!/bin/sh
set -e

# EduPath Railway container entrypoint.
#
# Shared by the Railway web service and the queue-worker service:
#   Web service CMD:    sh -c "php-fpm -D && nginx -g 'daemon off;'"
#   Worker service CMD: php artisan queue:work database --tries=3
#
# This entrypoint only performs environment-aware setup:
#   1. Renders the Nginx vhost from the build template using the
#      Railway-provided $PORT (skipped by the worker, which has no web surface).
#   2. Runs idempotent, non-destructive Laravel optimisations (config/route/
#      view/event caches) using runtime env values — safe to re-run on every
#      start, never touches the database.
# It never starts Nginx or PHP-FPM itself; that is the CMD's job. This keeps
# the worker service minimal: it only ever runs the queue worker.

# 1. Render the Nginx vhost template substituting $PORT (and only $PORT).
#    envsubst '$PORT' leaves Nginx's own $uri / $query_string / $document_root
#    untouched.
if [ -n "$PORT" ] && [ -f /etc/nginx/templates/default.conf.template ]; then
    echo "[edupath] Rendering Nginx config for PORT=$PORT"
    envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf
fi

# 2. Laravel production optimisations (idempotent, non-destructive).
if [ "$EDUPATH_SKIP_OPTIMIZE" != "1" ]; then
    echo "[edupath] Caching Laravel config, routes and views..."
    php artisan optimize --ansi >/dev/null 2>&1 || {
        echo "[edupath] optimize failed; falling back to config:cache only"
        php artisan config:cache --ansi >/dev/null 2>&1 || true
        php artisan route:cache --ansi >/dev/null 2>&1 || true
        php artisan view:cache --ansi >/dev/null 2>&1 || true
    }
fi

# 3. Hand control to the service's CMD (web: nginx+php-fpm, worker: queue:work).
exec "$@"