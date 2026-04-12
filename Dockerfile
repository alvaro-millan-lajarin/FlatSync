FROM php:8.4.3-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid
ARG targetPlatform

# Install system dependencies
RUN apt-get update && apt-get install -y  \
    build-essential \
    libssl-dev \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libfreetype6-dev \
    unzip \
    wget \
    zlib1g-dev \
    fontconfig \
    libxrender1 \
    xfonts-75dpi \
    xfonts-base

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath intl mysqli zip

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd

# Install WKHTMLTOPDF (QT patched)
RUN if [ "$targetPlatform" = "linux/arm/v7" ]; then ARCHITECTURE=arm; LIBJPEG_URL=https://mirrors.aliyun.com/ubuntu-ports/pool/main/libj/libjpeg-turbo/libjpeg-turbo8_2.0.3-0ubuntu1_armhf.deb; elif [ "$targetPlatform" = "linux/arm64" ]; then ARCHITECTURE=arm64; LIBJPEG_URL=http://ports.ubuntu.com/pool/main/libj/libjpeg-turbo/libjpeg-turbo8_2.0.3-0ubuntu1_arm64.deb; LIBSSL_URL=http://ports.ubuntu.com/pool/main/o/openssl/libssl1.1_1.1.1f-1ubuntu2_arm64.deb; else ARCHITECTURE=amd64; LIBJPEG_URL=http://archive.ubuntu.com/ubuntu/pool/main/libj/libjpeg-turbo/libjpeg-turbo8_2.0.3-0ubuntu1_amd64.deb; LIBSSL_URL=http://archive.ubuntu.com/ubuntu/pool/main/o/openssl/libssl1.1_1.1.1f-1ubuntu2_amd64.deb; fi \
    && wget ${LIBSSL_URL} \
    && dpkg -i libssl1.1_1.1.1f-1ubuntu2_${ARCHITECTURE}.deb \
    && wget ${LIBJPEG_URL} \
    && dpkg -i libjpeg-turbo8_2.0.3-0ubuntu1_${ARCHITECTURE}.deb \
    && wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.focal_${ARCHITECTURE}.deb \
    && dpkg -i wkhtmltox_0.12.6-1.focal_${ARCHITECTURE}.deb \
    && cp /usr/local/bin/wkhtmltopdf /usr/bin

# Install XDebug
RUN yes '' | pecl install xdebug \
    && docker-php-ext-enable xdebug

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user