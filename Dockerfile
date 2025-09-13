# Use PHP 8.2 FPM as base image
FROM php:8.2-fpm

# Set environment variables
ENV DOCUMENT_ROOT=/var/www/html/public
ENV PHP_MEMORY_LIMIT=1024M
ENV PHP_MAX_EXECUTION_TIME=300
ENV NODE_VERSION=20
ENV PYTHON_VERSION=3.11

# Set working directory
WORKDIR /var/www/html

# Install system dependencies, Nginx and Python
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    nginx \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    zip \
    unzip \
    python3 \
    python3-pip \
    python3-venv \
    python3-dev \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

# Configure PHP OPcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy package files
COPY package.json package-lock.json ./

# Install Node.js dependencies (including devDependencies for build)
RUN npm --version && node --version \
    && npm install --silent \
    && echo "Node modules installed successfully"

# Create Python virtual environment for better isolation  
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# Copy Python requirements and install in virtual environment
COPY requirements.txt* ./
RUN if [ -f requirements.txt ]; then \
        /opt/venv/bin/pip install --no-cache-dir -r requirements.txt; \
    else \
        /opt/venv/bin/pip install --no-cache-dir websocket-client requests; \
    fi

# Copy application files
COPY . .

# Verify dependencies and build frontend assets
RUN ls -la node_modules/laravel-vite-plugin || echo "laravel-vite-plugin not found" \
    && npm run build

# Remove devDependencies and clean npm cache to reduce image size
RUN npm prune --production \
    && npm cache clean --force

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod +x truedata*.py \
    && chmod +x *.sh

# Create Nginx configuration
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy PHP-FPM configuration
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Create PHP session directory
RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions

# Create .env file if it doesn't exist
RUN if [ -f .env.example ]; then cp .env.example .env; fi

# Generate application key (only if .env exists)
RUN if [ -f .env ]; then php artisan key:generate --no-interaction; fi

# Run composer scripts after copying all files
RUN composer run-script post-autoload-dump

# Create directories for logs and cache
RUN mkdir -p /var/log/websocket /var/log/scheduler /var/log/truedata /var/log/supervisor /var/log/nginx \
    && mkdir -p /var/cache/laravel /var/cache/redis \
    && chown -R www-data:www-data /var/log /var/cache

# Copy startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Copy WebSocket keep-alive script
COPY keep_websocket_alive.sh /usr/local/bin/keep_websocket_alive.sh
RUN chmod +x /usr/local/bin/keep_websocket_alive.sh

# Add health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Create supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose ports
EXPOSE 80 443 9000

# Start with supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
