# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install PostgreSQL extension required by Supabase
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copy application files to Apache root
COPY . /var/www/html/

# Enable Apache mod_rewrite for clean routing
RUN a2enmod rewrite

# Expose HTTP port
EXPOSE 80
