FROM php:8.4-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    mariadb-client \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j"$(nproc)" gd intl opcache pdo_mysql zip bcmath \
  && a2enmod rewrite headers expires remoteip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY site-platform/ /var/www/html/
COPY docker/site-platform/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/site-platform/prod-php.ini /usr/local/etc/php/conf.d/99-prod.ini
COPY docker/site-platform/entrypoint.sh /usr/local/bin/growbig-entrypoint

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
  && mkdir -p /var/www/html/web/sites/default/files /var/www/html/private \
  && chown -R www-data:www-data /var/www/html/web/sites/default/files /var/www/html/private \
  && chmod +x /usr/local/bin/growbig-entrypoint

ENTRYPOINT ["growbig-entrypoint"]
CMD ["apache2-foreground"]
