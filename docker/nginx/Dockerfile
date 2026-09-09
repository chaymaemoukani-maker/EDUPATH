# EduPath — Nginx serving container.
#
# Autonomous: no host bind mount. The full Laravel application (including the
# compiled public/ assets) is baked in by reusing the output of the "app"
# image built by `docker-compose.yml`. Nginx thus serves the exact same code
# and the same document root (/var/www/html) that PHP-FPM runs on, so the
# fastcgi SCRIPT_FILENAME resolves consistently.

FROM nginx:1.27-alpine

# Bake the Nginx vhost (root /var/www/html/public, proxy to app:9000).
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Reuse the application produced by the "app" build
# (image tag `edupath-app:latest` built first by docker compose).
COPY --from=edupath-app:latest /var/www/html /var/www/html

EXPOSE 80
