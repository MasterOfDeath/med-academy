FROM yiisoftware/yii2-php:8.4-apache

# Update packages and install necessary tools
RUN apt-get update && \
    apt-get install --no-install-recommends -y libhiredis-dev git && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    # Change ownership of the app directory
    chown -R www-data:www-data /app && \
    # Clear cache
    apt-get clean && rm -rf /var/lib/apt/lists/*
