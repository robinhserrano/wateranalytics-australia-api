#!/bin/bash
set -e

echo "🚀 Laravel Optimized Production Deployment Script"
echo "================================================"

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

# Step 1: Prepare Nginx configuration
echo "📝 Step 1/5: Preparing Nginx configuration..."
sed "s/yourdomain.com/$DOMAIN/g" deployment/nginx.conf > deployment/nginx-$DOMAIN.conf

# Step 2: SSL Check and Container Startup
echo "🔍 Step 2/5: Checking for SSL certificates..."
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

echo "🏗️  Step 3/5: Building and starting containers (using cache)..."
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d --remove-orphans

# Step 4: Wait for Database
echo "⏳ Step 4/5: Waiting for database to be healthy..."
while [ "$(docker inspect -f '{{.State.Health.Status}}' mysql-db)" != "healthy" ]; do
    echo "   (Waiting for MySQL...)"
    sleep 3
done
echo "✅ Database is ready!"

# Step 5: Application Optimizations
echo "⚡ Step 5/5: Running Laravel optimizations..."

# Consolidate Laravel commands to reduce overhead
docker compose exec -T app sh -c "
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    php artisan migrate --force && \
    php artisan optimize:clear && \
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

echo ""
echo "🔍 Final health check..."
if curl -s -I -k "https://localhost" | grep -q "200\|302\|301"; then
    echo "✅ Site is responding via HTTPS."
else
    echo "⚠️  Site responded, but check logs if you see a 502/500."
fi

# IMPORTANT: Force Nginx to reload upstream IPs so it doesn't return 502 Bad Gateway
echo "🔄 Reloading Nginx to clear upstream DNS cache..."
docker compose -f docker-compose.prod.yml restart web

echo ""
echo "✅ Deployment complete! https://$DOMAIN"