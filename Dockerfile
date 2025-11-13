FROM php:8.3-fpm

# Arguments
ARG user=laravel
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    default-mysql-client \
    libmagickwand-dev \
    imagemagick \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

# Install sodium
RUN apt-get update && apt-get install -y libsodium-dev \
    && docker-php-ext-install sodium \
    && rm -rf /var/lib/apt/lists/*

# Install and enable Imagick
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Xdebug (dev only, pode remover em produção)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www/html

# Copy application with correct permissions
COPY --chown=$user:$user . .

# Ajustar permissões para Laravel (storage e cache precisam ser graváveis)
RUN mkdir -p storage bootstrap/cache \
    && chown -R $user:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Change current user to laravel
USER $user

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
