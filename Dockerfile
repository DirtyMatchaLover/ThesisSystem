# Use an official PHP runtime with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions (MySQL, PDO, etc.)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache's rewrite module for clean URLs
RUN a2enmod rewrite

# Copy the application code into the container
COPY . /var/www/html/

# Set proper permissions for the uploads directory
RUN chown -R www-data:www-data /var/www/html/uploads