FROM docker.io/library/php:8.4-fpm-bookworm

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV WHATSAPP_WEB_NODE_BINARY=/usr/bin/node
ENV WHATSAPP_WEB_NPM_BINARY=/usr/bin/npm

# Install system dependencies and Chromium for Puppeteer / WhatsApp Gateway Sidecar
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    libsqlite3-dev \
    chromium \
    procps \
    ca-certificates \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Install official PHP Extension Installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    pdo_pgsql \
    bcmath \
    curl \
    mbstring \
    xml \
    zip \
    gd \
    intl \
    opcache \
    pcntl \
    posix \
    sodium \
    redis \
    exif

# Install Composer
COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js 22.x LTS & npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose PHP-FPM, WhatsApp Sidecar, and Vite ports
EXPOSE 9000 3000 5173

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
