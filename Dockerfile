FROM php:8.4-cli

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && apt-get update \
    && apt-get install -y --no-install-recommends git unzip curl sqlite3 default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions gd pdo_mysql pdo_sqlite intl zip bcmath pcntl exif opcache pcov

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
