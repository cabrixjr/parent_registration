# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install PostgreSQL extension and Zip extension (required for reading .xlsx files)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Copy application files to Apache root
COPY . /var/www/html/

# Enable Apache mod_rewrite for clean routing
RUN a2enmod rewrite

# Expose HTTP port
EXPOSE 80
