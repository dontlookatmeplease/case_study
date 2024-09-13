# Using base PHP with Apache
FROM php:apache

# Increase upload_max_filesize in PHP
RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/uploads.ini

# Set up working directory
WORKDIR /var/www/html

# Installing PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    nano \
    && docker-php-ext-install intl pdo_mysql zip mysqli

# Set public as DocumentRoot
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Composer installation
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files into Docker image
COPY . /var/www/html

# Install dependencies with Composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Allow Apache mod_rewrite (Symfony URL routing)
RUN a2enmod rewrite

# Permissions for Symfony folders
RUN chown -R www-data:www-data /var/www/html/var
RUN chown -R www-data:www-data /var/www/html/public

# Apache runs on port 80
EXPOSE 80