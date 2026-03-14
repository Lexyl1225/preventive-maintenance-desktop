FROM php:8.2-apache

# Install system dependencies and PHP extensions used by the app
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev zlib1g-dev libonig-dev \
    && docker-php-ext-install pdo_mysql zip mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Allow .htaccess overrides (needed for clean URLs)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# PHP runtime configuration (adjust as needed)
RUN { \
    echo "upload_max_filesize = 200M"; \
    echo "post_max_size = 200M"; \
    echo "memory_limit = 512M"; \
    echo "date.timezone = Asia/Shanghai"; \
} > /usr/local/etc/php/conf.d/epm.ini

# Copy application into Apache webroot
COPY . /var/www/html

# Ensure uploads folder exists and is writable by the webserver user
RUN mkdir -p /var/www/html/uploads /var/www/html/uploads/trash \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
