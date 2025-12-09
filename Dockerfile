# Base stage - install system dependencies
FROM yiisoftware/yii2-php:8.4-apache AS base

# hadolint ignore=DL3008
RUN apt-get update && \
    apt-get install --no-install-recommends -y \
        libhiredis-dev \
        git && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    # Clear cache
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Dependencies stage - install PHP dependencies
FROM base AS dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist

# Final stage - copy application files
FROM dependencies AS final
COPY . .
RUN chown -R www-data:www-data /app
