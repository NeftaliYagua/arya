FROM php:7.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    mariadb-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libmagickwand-dev --no-install-recommends

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath

RUN apt-get install ghostscript -y \
    && pecl install imagick \
    && docker-php-ext-enable imagick

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get install gnupg -yq \
    && curl -sL https://deb.nodesource.com/setup_18.x | bash \
    && apt-get install nodejs -yq \
    && curl -L https://npmjs.org/install.sh | bash

# Clear apt cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
