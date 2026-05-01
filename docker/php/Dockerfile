FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    libzip-dev libpq-dev libicu-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    supervisor \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets intl opcache

RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer --2

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
