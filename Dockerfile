FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y git zip unzip libpng-dev default-mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www

# Copy project files into the container
COPY . /var/www

# Update Apache configuration to point to public directory (adjust if needed)
# For Symfony, it's typically /var/www/public
# For other PHP frameworks like Laravel, it might be /var/www/public_html or similar
ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


# Install Composer (You can also consider copying an existing composer.phar)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install project dependencies
RUN composer install