FROM php:8.2-cli-alpine

# System deps + PHP extensions (Laravel + DomPDF)
RUN apk add --no-cache \
    bash git unzip curl \
    libpng libpng-dev libjpeg-turbo-dev freetype-dev \
    oniguruma-dev libzip-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql gd mbstring zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Install vendors (prod)
RUN composer install --no-interaction --no-progress --prefer-dist --no-dev --optimize-autoloader

# Make writable dirs
RUN mkdir -p storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV PORT=8080
EXPOSE 8080

# NOTE: do NOT cache config at build time. We’ll do it at runtime after
# Render injects env vars (APP_KEY, etc).
CMD ["/bin/sh", "/entrypoint.sh"]
