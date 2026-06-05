FROM php:8.2-apache

# Install PostgreSQL extensions for PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files to the Apache document root
COPY . /var/www/html/

# Ensure the uploads directory exists and is writable
RUN mkdir -p /var/www/html/assets/uploads && chmod -R 777 /var/www/html/assets/uploads

# Expose port 80
EXPOSE 80
