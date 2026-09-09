# ============================================================================
# EduPath — Production-ready, autonomous Laravel image (multi-stage)
# The final image ships the full Laravel app (vendor/ + public/build/ baked in)
# and requires NO bind mount of the source code at runtime.
# ============================================================================

# ----------------------------------------------------------------------------
# Stage 1 — PHP dependencies (Composer)
# ----------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Leverage layer caching: install dependencies using only the manifests first.
COPY composer.json composer.lock ./

# --no-scripts: defer post-autoload-dump (package discovery) until the full
#               application is present so artisan can actually run.
# --no-autoloader + later full install: keep the first pass minimal & cached.
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
        --no-autoloader

# Copy the entire application source into the build stage.
COPY . .

# Re-run now that the application is present. Scripts are executed (the
# post-autoload-dump hook runs `php artisan package:discover`), regenerating
# bootstrap/cache/packages.php + services.php deterministically *inside* the
# image instead of inheriting any host-local state.
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ----------------------------------------------------------------------------
# Stage 2 — Frontend assets (Node / Vite / Tailwind)
# ----------------------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

# Copy manifests first for good layer caching.
COPY package.json package-lock.json ./
RUN npm ci

# Copy the whole application (respecting .dockerignore). The Blade views under
# resources/views are scanned by Tailwind v3 to generate the CSS utility
# classes — they must be present during `npm run build`.
COPY . .

RUN npm run build

# ----------------------------------------------------------------------------
# Stage 3 — Runtime (PHP-FPM)
# ----------------------------------------------------------------------------
FROM php:8.4-fpm AS runtime

# System dependencies required by the PHP extensions and the app itself.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        zip \
        unzip \
        libzip-dev \
        procps \
        ca-certificates \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions required by EduPath (matches the previous single-stage image).
RUN docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# OPcache for production performance.
RUN docker-php-ext-install opcache

# Production-oriented PHP settings.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'expose_php=0'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=25M'; \
        echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/edupath.ini

WORKDIR /var/www/html

# Bake the full application (vendor/ included) into the image.
COPY --from=vendor /app /var/www/html

# Bake the compiled frontend assets (public/build/) into the image.
COPY --from=frontend /app/public/build /var/www/html/public/build

# Bootstrap/cache and storage must be writable by www-data (the PHP-FPM user).
# Reasonable 775 layout — NOT a blanket 777 on the whole application.
RUN mkdir -p \
        /var/www/html/storage/framework/cache/data \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs \
        /var/www/html/storage/app/public \
        /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Recreate the public storage symlink (excluded from the build context as the
# host reparse point is not portable into the image).
RUN ln -s /var/www/html/storage/app/public /var/www/html/public/storage

# Entrypoint: apply non-destructive Laravel optimizations at container start
# (when runtime env vars are available) then run the requested command.
COPY docker/entrypoint.sh /usr/local/bin/edupath-entrypoint
RUN chmod +x /usr/local/bin/edupath-entrypoint

ENTRYPOINT ["edupath-entrypoint"]

EXPOSE 9000

CMD ["php-fpm"]
