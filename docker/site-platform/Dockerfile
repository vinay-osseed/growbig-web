# syntax=docker/dockerfile:1.7

FROM php:8.4-apache-bookworm AS php-base

ENV APACHE_DOCUMENT_ROOT=/var/www/html/site-platform/web \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/var/www/html/site-platform/vendor/bin:${PATH}"

RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    default-mysql-client \
    git \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    zip; \
  docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
  docker-php-ext-install -j"$(nproc)" \
    bcmath \
    gd \
    intl \
    opcache \
    pdo_mysql \
    zip; \
  a2enmod rewrite headers expires remoteip; \
  rm -rf /var/lib/apt/lists/*

COPY docker/site-platform/prod-php.ini /usr/local/etc/php/conf.d/zz-growbig-prod.ini
COPY docker/site-platform/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

FROM php-base AS build

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY site-platform/composer.json site-platform/composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY site-platform/ ./
COPY scripts/setup/ ./scripts/setup/

RUN composer dump-autoload --optimize --no-dev

FROM php-base AS runtime

WORKDIR /var/www/html

COPY --from=build /app /var/www/html/site-platform
COPY docker/site-platform/entrypoint.sh /usr/local/bin/growbig-entrypoint

RUN set -eux; \
  chmod +x /usr/local/bin/growbig-entrypoint; \
  test -f /var/www/html/site-platform/web/core/includes/bootstrap.inc; \
  test -f /var/www/html/site-platform/vendor/bin/drush; \
  test -d /var/www/html/site-platform/scripts/setup; \
  mkdir -p \
    /var/www/html/site-platform/web/sites/default/files \
    /var/www/html/site-platform/private \
    /var/www/html/site-platform/config/sync; \
  chown -R www-data:www-data \
    /var/www/html/site-platform/web/sites/default/files \
    /var/www/html/site-platform/private \
    /var/www/html/site-platform/config

EXPOSE 80

ENTRYPOINT ["growbig-entrypoint"]
CMD ["apache2-foreground"]
