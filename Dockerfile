FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev \
    zip \
    unzip

# Install PHP extensions required by Laravel
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml \
    intl

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Set permissions script that runs at startup
RUN echo '#!/bin/sh' > /usr/local/bin/entrypoint.sh && \
    echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true' >> /usr/local/bin/entrypoint.sh && \
    echo 'exec php-fpm' >> /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

# Use entrypoint to set permissions then start php-fpm
# vendor/ comes from the host volume mount (dev) or COPY (prod)
CMD ["/usr/local/bin/entrypoint.sh"]
