FROM php:8.3-cli-alpine

# Install system dependencies & PostgreSQL dev libraries
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    linux-headers \
    netcat-openbsd \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    opcache \
    gd \
    zip \
    pcntl \
    intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy php configuration
COPY docker/backend/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy entrypoint script
COPY docker/backend/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application source
COPY . /var/www/html

# Run composer install during build
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader || true

# Expose port
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
