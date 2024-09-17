FROM php:8.2-apache AS builder

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the application files to the container
COPY . .

# Install any dependencies your PHP application needs
RUN apt-get update && \
    apt-get install -y libpq-dev zip unzip \
    # Add any dependencies here \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (if your project uses Composer)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql

# Install additional tools required for Composer
RUN apt-get update && \
    apt-get install -y git && \
    rm -rf /var/lib/apt/lists/*

# Set up Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer

# Run composer install to install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader


FROM php:8.3-fpm-alpine

# install composer
#RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN apk update && \
	apk add --no-cache postgresql-dev gcc make curl

RUN docker-php-ext-install pdo_pgsql

# install dependencies
##COPY composer.json composer.lock .
##RUN composer install --no-scripts

# copy project files
WORKDIR /var/www/html
COPY . .

# copy dependencies
COPY --from=builder /var/www/html/vendor .

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database