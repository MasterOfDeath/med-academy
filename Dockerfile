FROM yiisoftware/yii2-php:8.4-apache

# Set build arguments
ARG USER_ID=1000
ARG GROUP_ID=100

# Update packages and install necessary tools
RUN apt-get update && \
    apt-get install --no-install-recommends -y libhiredis-dev git && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    # Create user with specific UID/GID
    groupmod -g $GROUP_ID -o www-data && \
    usermod -u $USER_ID -g $GROUP_ID -o www-data && \
    # Change ownership of the app directory
    chown -R www-data:www-data /app && \
    # Clear cache
    apt-get clean && rm -rf /var/lib/apt/lists/*