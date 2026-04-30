FROM php:8.2-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash \
    git \
    curl \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    autoconf \
    g++ \
    make \
    linux-headers

# PHP extensions required by Laravel + MySQL/MariaDB
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
        pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Recommended PHP settings
RUN { \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=20M'; \
    echo 'post_max_size=20M'; \
    echo 'max_execution_time=120'; \
    echo 'date.timezone=Europe/Madrid'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.validate_timestamps=1'; \
} > /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

# Non-root user matching common host UID
RUN addgroup -g 1000 app && adduser -D -u 1000 -G app app \
 && chown -R app:app /var/www/html
USER app

EXPOSE 9000
CMD ["php-fpm"]
