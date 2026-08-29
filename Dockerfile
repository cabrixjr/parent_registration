# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install PostgreSQL, Zip, and session dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Set proper working directory and permissions for PHP sessions
RUN mkdir -p /var/lib/php/sessions && \
    chown -R www-data:www-data /var/lib/php/sessions /var/www/html

# Copy application files to Apache root
COPY . /var/www/html/

# Enable Apache mod_rewrite for routing
RUN a2enmod rewrite

# Expose HTTP port
EXPOSE 80
