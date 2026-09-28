FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    gnupg \
    ca-certificates

# Install Node.js 18.x
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (without running scripts yet)
RUN composer install --no-scripts --no-autoloader --no-dev --prefer-dist

# Copy package files for Node.js dependencies
COPY package*.json ./

# Install Node.js dependencies (including dev dependencies for build)
RUN npm ci

# Copy existing application directory contents
COPY . /var/www

# Complete composer installation
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets with Vite
RUN npm run build

# Remove dev dependencies after build to reduce image size
RUN npm prune --production

# Create necessary directories with proper permissions
RUN mkdir -p /var/www/public/upload \
    /var/www/storage/logs \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/app/public \
    /var/www/bootstrap/cache

# Ensure Laravel system files are writable
RUN touch /var/www/storage/logs/laravel.log && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/upload && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public/upload

# Clear development cache artifacts to prevent "Class Not Found" errors
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan view:clear && \
    php artisan route:clear

# Expose port 80
EXPOSE 80

# Create Internal Configuration Files (Inline)
RUN printf 'server {\n\
    listen 80;\n\
    listen [::]:80;\n\
    server_name localhost;\n\
    root /var/www/public;\n\
    index index.php index.html;\n\
    charset utf-8;\n\
    client_max_body_size 100M;\n\
    add_header X-Frame-Options "SAMEORIGIN" always;\n\
    add_header X-Content-Type-Options "nosniff" always;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \\.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
        fastcgi_param HTTPS "on";\n\
        fastcgi_param HTTP_X_FORWARDED_PROTO https;\n\
        fastcgi_read_timeout 300;\n\
    }\n\
    location ~ /\\. {\n\
        deny all;\n\
    }\n\
}\n' > /etc/nginx/sites-available/default

RUN printf '[supervisord]\n\
nodaemon=true\n\
user=root\n\
logfile=/var/log/supervisor/supervisord.log\n\
[program:laravel-cleanup]\n\
command=/bin/bash -c "rm -f /var/www/bootstrap/cache/*.php && rm -f /var/www/storage/framework/views/*.php && php /var/www/artisan config:clear"\n\
autostart=true\n\
autorestart=false\n\
priority=1\n\
[program:php-fpm]\n\
command=/usr/local/sbin/php-fpm -F\n\
autostart=true\n\
autorestart=true\n\
priority=5\n\
[program:nginx]\n\
command=/usr/sbin/nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
priority=10\n' > /etc/supervisor/conf.d/supervisord.conf

RUN printf 'upload_max_filesize = 100M\n\
post_max_size = 100M\n\
memory_limit = 512M\n\
max_execution_time = 300\n\
date.timezone = Asia/Jakarta\n\
opcache.enable = 1\n\
opcache.enable_cli = 1\n' > /usr/local/etc/php/conf.d/custom.ini

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]