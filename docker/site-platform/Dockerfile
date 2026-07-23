# syntax=docker/dockerfile:1.7

FROM composer:2 AS vendor
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY site-platform/composer.json site-platform/composer.lock ./
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY site-platform/ ./
RUN composer dump-autoload --optimize --no-dev

FROM php:8.4-apache-bookworm AS runtime

ENV APACHE_DOCUMENT_ROOT=/var/www/html/web \
    DRUPAL_ENV=prod \
    COMPOSER_HOME=/tmp/composer

RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
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

COPY docker/site-platform/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/site-platform/prod-php.ini /usr/local/etc/php/conf.d/zz-site-platform-prod.ini
COPY docker/site-platform/entrypoint.sh /usr/local/bin/site-platform-entrypoint

COPY --from=vendor --chown=www-data:www-data /app /var/www/html

RUN set -eux; \
  chmod +x /usr/local/bin/site-platform-entrypoint; \
  mkdir -p /var/www/html/web/sites/default/files /var/www/html/private /var/www/html/tmp; \
  chown -R www-data:www-data /var/www/html/web/sites/default/files /var/www/html/private /var/www/html/tmp; \
  find /var/www/html -type d -exec chmod 755 {} \; ; \
  find /var/www/html -type f -exec chmod 644 {} \; ; \
  chmod +x /usr/local/bin/site-platform-entrypoint

WORKDIR /var/www/html
EXPOSE 80
ENTRYPOINT ["site-platform-entrypoint"]
CMD ["apache2-foreground"]
