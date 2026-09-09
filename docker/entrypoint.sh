#!/bin/sh
set -e

# EduPath container entrypoint.
#
# Applies lightweight, non-destructive Laravel production optimisations at
# container start, when the runtime environment variables (DB, APP_KEY, ...)
# are already available. This cannot be done at build time because the env
# values are injected at runtime, never baked into the image.
#
# All cache commands are idempotent and safe to re-run on every start. Nothing
# here ever touches the database or user data.

if [ "$EDUPATH_SKIP_OPTIMIZE" != "1" ]; then
    echo "[edupath] Caching Laravel config, routes and views..."
    php artisan optimize --ansi >/dev/null 2>&1 || {
        echo "[edupath] optimize failed; falling back to config:cache only"
        php artisan config:cache --ansi >/dev/null 2>&1 || true
        php artisan route:cache --ansi >/dev/null 2>&1 || true
        php artisan view:cache --ansi >/dev/null 2>&1 || true
    }
fi

# Execute the container's main command (php-fpm, queue:work, artisan, shell...).
exec "$@"
