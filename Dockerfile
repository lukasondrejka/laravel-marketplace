FROM php:8.4-cli

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && apt-get update \
    && apt-get install -y --no-install-recommends git unzip curl sqlite3 default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions gd pdo_mysql pdo_sqlite intl zip bcmath pcntl exif opcache pcov sockets

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js (for the Playwright driver used by the Pest browser plugin) and Chromium for E2E tests.
# The Playwright version comes from package-lock.json so the browser build always matches the npm package.
COPY --from=node:22-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
COPY package.json package-lock.json /tmp/playwright-install/
RUN cd /tmp/playwright-install \
    && npm ci --ignore-scripts --no-audit --no-fund \
    && npx playwright install --with-deps chromium \
    && rm -rf /tmp/playwright-install /var/lib/apt/lists/* /root/.npm
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
