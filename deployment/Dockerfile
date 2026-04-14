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

# Install system dependencies and PHP extensions using the robust installer
# This handles all Alpine library dependencies and contains fixes for known build bugs, 
# specifically for ARM64 (AARCH64) architectures.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql mbstring exif pcntl bcmath gd zip opcache curl xml dom intl redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy built vendor dependencies from Stage 0 (generator) 
# to avoid re-downloading and potential build-time failures
COPY --from=generator /app/vendor /var/www/vendor

# Copy built frontend assets from Stage 1 (frontend)
COPY --from=frontend /app/public/build /var/www/public/build

# Copy custom PHP configuration
COPY deployment/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Create minimal .env if it doesn't exist for build-time commands (package:discover)
# We use a robust random string for the build-key.
RUN if [ ! -f ".env" ]; then \
      echo "APP_KEY=base64:$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1 | base64)" > .env; \
      echo "APP_ENV=production" >> .env; \
      echo "DB_CONNECTION=sqlite" >> .env; \
      echo "DB_DATABASE=:memory:" >> .env; \
    fi

# Ensure all dependencies are optimized and scripts run (like package discovery)
# We use --no-dev for production. This will fail the build if discovery fails, 
# giving us clear visibility into any issues.
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
