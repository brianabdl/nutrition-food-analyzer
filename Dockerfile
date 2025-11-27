# Use official PHP with Apache
FROM php:8.1-apache
# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (focus on MySQL/MySQLi)
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Set development environment variable
ENV ENVIRONMENT=development

# Copy application files first (only needed for initial build)
# In development, files are mounted via docker-compose volumes
COPY . /var/www/html/

# Copy custom Apache configuration if exists
RUN if [ -f /var/www/html/docker/apache-config.conf ]; then \
        cp /var/www/html/docker/apache-config.conf /etc/apache2/sites-available/000-default.conf; \
    fi

# Copy custom PHP configuration if exists
RUN if [ -f /var/www/html/docker/php.ini ]; then \
        cp /var/www/html/docker/php.ini /usr/local/etc/php/conf.d/custom.ini; \
    fi

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/data \
    && mkdir -p /var/www/html/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Note: In development, permissions are handled by docker-compose volume mounts
# The mounted ./: directory will have your host user permissions

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
