FROM php:8.2-fpm

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update \
    && apt-get install -y \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libzip-dev \
        zip \
        unzip \
        libicu-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy Composer binary from the official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Install Node.js for running Vite and compiling assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

CMD ["php-fpm"]
