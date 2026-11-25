FROM php:8.5-apache AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install bcmath gd intl pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

FROM php-base AS app-build

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader \
    && mkdir -p storage/app/private storage/app/public \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache

FROM node:24-alpine AS frontend

WORKDIR /app

COPY package.json vite.config.js ./
COPY resources ./resources
COPY app/Filament ./app/Filament
COPY public ./public
COPY --from=app-build /var/www/html/vendor ./vendor

RUN npm install --no-audit --no-fund \
    && npm run build

FROM php-base AS runtime

WORKDIR /var/www/html

COPY --from=app-build /var/www/html /var/www/html

RUN a2enmod rewrite \
    && sed -i 's#DocumentRoot /var/www/html$#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' \
        '<Directory /var/www/html/public>' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' >> /etc/apache2/apache2.conf

COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

EXPOSE 80

CMD ["apache2-foreground"]
