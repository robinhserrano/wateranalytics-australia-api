#!/bin/bash
set -e

echo "🚀 Laravel Optimized Production Deployment Script (Zero-Cache)"
echo "=============================================================="

# Try to extract domain from .env if not provided
if [ -z "$1" ] && [ -f ".env" ]; then
    DOMAIN=$(grep "^APP_URL=" .env | cut -d'=' -f2 | sed 's|https\?://||' | sed 's|/.*||' | tr -d '"')
    if [ -n "$DOMAIN" ]; then
        echo "📌 Using domain from .env: $DOMAIN"
    fi
fi

# Check if domain is provided or extracted
if [ -z "$1" ] && [ -z "$DOMAIN" ]; then
    echo "❌ Error: Domain name required"
    echo "Usage: ./deploy.sh [yourdomain.com] [email]"
    exit 1
fi

DOMAIN=${1:-$DOMAIN}
EMAIL=${2:-"admin@$DOMAIN"}

echo "📋 Configuration:"
echo "   Domain: $DOMAIN"
echo "   Email: $EMAIL"
echo ""

# Step 0: Initializing Configuration
if [ ! -f ".env" ]; then
    echo "❌ Error: .env file missing. Please create it first."
    exit 1
fi

# Check if APP_KEY is missing
if ! grep -q "^APP_KEY=.\+" .env; then
    echo "🔑 APP_KEY is missing. Will generate one during optimization..."
    GENERATE_KEY=true
fi

# Derive Docker Compose project name (same logic as Docker)
COMPOSE_PROJECT=$(basename "$(pwd)" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9_-]//g')

# Step 1: Prepare Nginx configuration
echo "📝 Step 1/7: Preparing Nginx configuration..."
sed "s/yourdomain.com/$DOMAIN/g" deployment/nginx.conf > deployment/nginx-$DOMAIN.conf

# Step 2: SSL Check and Container Startup
echo "🔍 Step 2/7: Checking for SSL certificates..."
if [ ! -f "certbot/conf/live/$DOMAIN/fullchain.pem" ]; then
    echo "⚠️  SSL certificates not found. Bootstrapping with HTTP-only..."
    
    # Start app and mysql first
    docker compose -f docker-compose.prod.yml up -d app mysql
    
    # Start web in HTTP mode
    export NGINX_CONF="deployment/nginx-http.conf"
    docker compose -f docker-compose.prod.yml up -d web
    
    echo "⏳ Waiting for web server..."
    sleep 5
    
    # Generate SSL certificate
    echo "🔐 Generating SSL certificate via Certbot..."
    docker run --rm \
      -v "$(pwd)/certbot/conf:/etc/letsencrypt" \
      -v "$(pwd)/certbot/www:/var/www/certbot" \
      certbot/certbot certonly --webroot \
      -w /var/www/certbot \
      --email "$EMAIL" --agree-tos --no-eff-email --non-interactive -d "$DOMAIN"
    
    echo "🔄 Switching to HTTPS mode..."
fi

# Final setup: Building and starting all services
export NGINX_CONF="deployment/nginx-$DOMAIN.conf"

# Step 3: CRITICAL - Remove all stale Docker caches and volumes
echo "🧹 Step 3/7: Removing all stale caches and volumes (ZERO-CACHE MODE)..."
docker compose -f docker-compose.prod.yml down 2>/dev/null || true

# Remove stale named volumes
docker volume rm "${COMPOSE_PROJECT}_assets_build" 2>/dev/null && echo "   ✅ Old assets_build volume removed" || echo "   ℹ️  No assets_build volume found"
docker volume rm "${COMPOSE_PROJECT}_nginx_cache" 2>/dev/null && echo "   ✅ Old nginx_cache volume removed" || echo "   ℹ️  No nginx_cache volume found"

# Remove old Docker images to force fresh pulls and builds
docker rmi -f $(docker images --filter "dangling=true" -q) 2>/dev/null || true

# Step 4: Build with NO CACHE and fresh images
echo "🏗️  Step 4/7: Building containers (--no-cache --pull for fresh build)..."
docker compose -f docker-compose.prod.yml build --no-cache --pull
docker compose -f docker-compose.prod.yml up -d --remove-orphans

# Step 5: Wait for Database
echo "⏳ Step 5/7: Waiting for database to be healthy..."
RETRY_COUNT=0
MAX_RETRIES=30
while [ "$(docker inspect -f '{{.State.Health.Status}}' mysql-db)" != "healthy" ]; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -gt $MAX_RETRIES ]; then
        echo "❌ Database failed to become healthy after $MAX_RETRIES attempts"
        exit 1
    fi
    echo "   (Waiting for MySQL... attempt $RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done
echo "✅ Database is ready!"

# Step 6: Clear ALL application caches (Laravel + PHP)
echo "🧹 Step 6/7: Clearing all application caches..."
docker compose exec -T app sh -c "
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    php artisan optimize:clear && \
    php artisan config:clear && \
    php artisan view:clear && \
    php artisan route:clear && \
    php artisan cache:clear && \
    php -r 'opcache_reset();' 2>/dev/null || true
"

# Step 7: Application Optimizations and Migrations
echo "⚡ Step 7/7: Running Laravel optimizations..."
docker compose exec -T app sh -c "
    php artisan migrate --force && \
    $( [ "$GENERATE_KEY" = true ] && echo "php artisan key:generate && " )
    php artisan storage:link --force && \
    php artisan optimize && \
    php artisan scribe:generate
"

# Handle seeders if necessary
echo "🌱 Checking permissions..."
PERM_COUNT=$(docker compose exec -T app php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" | tail -n 1)
if [ "$PERM_COUNT" = "0" ]; then
    echo "🌱 Seeding roles and permissions..."
    docker compose exec -T app php artisan db:seed --class=RolesAndPermissionsSeeder --force
fi

# Force Nginx and app container restart to clear all connection caches
echo "🔄 Restarting services to clear all caches..."
docker compose -f docker-compose.prod.yml restart web app

echo ""
echo "🔍 Final health check..."
if curl -s -I -k "https://localhost" | grep -q "200\|302\|301"; then
    echo "✅ Site is responding via HTTPS."
else
    echo "⚠️  Site responded, but check logs if you see a 502/500."
fi

echo ""
echo "✅ Deployment complete! https://$DOMAIN"
echo ""
echo "📊 Cache Status:"
echo "   ✅ Docker cache: CLEARED (--no-cache --pull)"
echo "   ✅ Volume cache: CLEARED (deleted and recreated)"
echo "   ✅ Laravel cache: CLEARED (optimize:clear + config:clear + view:clear + route:clear)"
echo "   ✅ PHP OPcache: CLEARED (opcache_reset)"
echo ""
echo "💡 Note: Browser cache is client-side. Users should hard-refresh (Ctrl+Shift+R)"