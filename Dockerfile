FROM php:8.4-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    autoconf \
    bash \
    curl \
    g++ \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    make \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    supervisor

# PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp && \
    docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# PHP production settings
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

# Application files
COPY . .

# Install PHP dependencies (production: no dev)
RUN composer install --optimize-autoloader --no-dev 2>/dev/null || true

# Install Node dependencies and build assets
RUN npm ci && npm run build 2>/dev/null || true

# Create log directories
RUN mkdir -p /var/log/supervisor /var/log/php

# Storage permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
