# Production Dockerfile for Laravel

# Stage 0: Generate Wayfinder route files (if available)
FROM composer:latest as generator
WORKDIR /app
COPY composer.json composer.lock ./
# Install dependencies but skip scripts until app files are present
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts
COPY . .

# Create minimal .env for build-time commands (wayfinder, key generation, etc)
RUN if [ ! -f ".env" ]; then \
      echo "APP_KEY=" > .env; \
      echo "APP_DEBUG=false" >> .env; \
      echo "APP_ENV=production" >> .env; \
      echo "DB_CONNECTION=sqlite" >> .env; \
    fi

# Generate APP_KEY if empty
RUN php artisan key:generate --env=.env 2>/dev/null || true

# Regenerate autoload and run package discovery
RUN composer dump-autoload --optimize --no-interaction \
 && php artisan package:discover --ansi || true

# Generate wayfinder routes with verbose output
RUN echo "🔄 Generating wayfinder routes..." \
 && (php artisan wayfinder:build 2>&1 || php artisan wayfinder:generate --with-form 2>&1) \
 && echo "✅ Wayfinder generation complete" || echo "⚠️  Wayfinder generation attempted"

# Verify critical route files exist
RUN if [ -f "resources/js/routes/login/index.ts" ]; then \
      echo "✅ Login routes generated"; \
    else \
      echo "⚠️  Login routes NOT found"; \
    fi

# Stage 1: Build Frontend Assets
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# Copy generated route helpers from generator (if created)
COPY --from=generator /app/resources/js/wayfinder /app/resources/js/wayfinder
COPY --from=generator /app/resources/js/routes /app/resources/js/routes
# Skip wayfinder generation during build since PHP is not available in Node Alpine
# (files are already pre-generated in the generator stage)
RUN SKIP_WAYFINDER=true npm run build

# Stage 2: Serve Application
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY deployment/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build /var/www/public/build

# Install dependencies if vendor doesn't exist (useful for clean installs)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
