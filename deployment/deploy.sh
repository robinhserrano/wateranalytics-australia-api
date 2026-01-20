#!/bin/bash
set -e

echo "🚀 Laravel Production Deployment Script"
echo "========================================"

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
    echo "Or set APP_URL in .env file"
    exit 1
fi

DOMAIN=${1:-$DOMAIN}
EMAIL=${2:-"admin@$DOMAIN"}

echo "📋 Configuration:"
echo "   Domain: $DOMAIN"
echo "   Email: $EMAIL"
echo ""

# Step 0: Verify .env has correct settings for Docker
if [ -f ".env" ]; then
    # 1. Check DB_HOST
    DB_HOST_VAL=$(grep "^DB_HOST=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'" | tr -d '\r')
    if [ "$DB_HOST_VAL" != "mysql" ] && [ "$DB_HOST_VAL" != "db" ] && [ "$DB_HOST_VAL" != "mysql-db" ]; then
        echo "⚠️  Warning: DB_HOST in .env is set to '$DB_HOST_VAL'."
        echo "   For Docker production, it should usually be 'mysql' (the service name)."
        echo "   Please update your .env file if the connection fails."
        echo ""
    fi

    # 2. Check and Generate APP_KEY if missing
    if ! grep -q "^APP_KEY=.\+" .env; then
        echo "🔑 APP_KEY is missing. Generating one..."
        # We mark that we need to generate it later inside the container.
        GENERATE_KEY=true
    fi
fi 
# ^ FIX 1: Removed extra 'fi' here

# Step 1: Create domain-specific Nginx config
echo "📝 Step 1/6: Creating Nginx configuration for $DOMAIN..."
sed "s/yourdomain.com/$DOMAIN/g" deployment/nginx.conf > deployment/nginx-$DOMAIN.conf

# Step 2: Check if SSL certificates exist
echo "🔍 Step 2/6: Checking for SSL certificates..."
if [ ! -f "certbot/conf/live/$DOMAIN/fullchain.pem" ]; then
    echo "⚠️  SSL certificates not found. Starting with HTTP-only mode..."
    
    # Update docker-compose to use HTTP config
    sed "s|deployment/nginx-http.conf|deployment/nginx-http.conf|g" docker-compose.prod.yml > docker-compose.temp.yml
    
    # Start containers with HTTP config
    echo "🐳 Step 3/6: Starting containers (HTTP mode)..."
    docker compose -f docker-compose.temp.yml up -d
    
    # Wait for containers to be ready
    echo "⏳ Waiting for services to start..."
    sleep 10
    
    # Generate SSL certificate
    echo "🔐 Step 4/6: Generating SSL certificate..."
    docker run --rm \
      -v $(pwd)/certbot/conf:/etc/letsencrypt \
      -v $(pwd)/certbot/www:/var/www/certbot \
      certbot/certbot certonly --webroot \
      -w /var/www/certbot \
      --email $EMAIL \
      --agree-tos \
      --no-eff-email \
      --non-interactive \
      -d $DOMAIN
    
    # Switch to HTTPS config
    echo "🔄 Step 5/6: Switching to HTTPS mode..."
    sed "s|deployment/nginx-http.conf|deployment/nginx-$DOMAIN.conf|g" docker-compose.prod.yml > docker-compose.temp.yml
    echo "🏗️  Building Docker images..."
    docker compose -f docker-compose.temp.yml build --no-cache
    
    docker compose -f docker-compose.temp.yml up -d --force-recreate
else
    echo "✅ SSL certificates found! Using HTTPS mode..."
    sed "s|deployment/nginx-http.conf|deployment/nginx-$DOMAIN.conf|g" docker-compose.prod.yml > docker-compose.temp.yml
    
    echo "🏗️  Building Docker images..."
    docker compose -f docker-compose.temp.yml build --no-cache
    
    docker compose -f docker-compose.temp.yml up -d --force-recreate
fi

# Start/restart all containers with explicit .env file
echo "🐳 Step 6/6: Starting all containers..."
docker compose --env-file .env -f docker-compose.temp.yml up -d --force-recreate

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
set +e # Temporarily disable 'stop on error' for the polling loop
for i in {1..30}; do
    # 1. Diagnostic: Check if .env is a file or directory
    ENV_CHECK=$(docker compose -f docker-compose.temp.yml exec -T app stat -c "%F %a %u:%g" .env 2>&1)
    if [ $i -eq 1 ]; then
        echo "   🔍 .env info in container: $ENV_CHECK"
    fi

    # Try to connect and capture error
    ERROR_MSG=$(docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan migrate:status 2>&1)
    EXIT_CODE=$?
    
    # 3. Check for actual success (migrate:status output starts with specific headers)
    if [ $EXIT_CODE -eq 0 ] && echo "$ERROR_MSG" | grep -q "Migration name"; then
        echo "✅ Database is ready!"
        DB_READY=true
        break
    fi
    
    # 4. Debug info: Check MySQL health if failing
    if [ $i -eq 5 ]; then
        echo "   🔍 MySQL status check (Attempt 5):"
        docker ps --filter "name=mysql-db" --format "{{.Status}}"
        echo "   --- Recent MySQL Logs ---"
        docker logs mysql-db --tail 10
    fi
    
    # 4. Debug info: show what the app thinks its DB_HOST is
    if [ $i -eq 1 ]; then
        CONTAINER_DB_HOST=$(docker compose -f docker-compose.temp.yml exec -T app php artisan tinker --execute="echo env('DB_HOST');" 2>/dev/null | tail -n 1 || echo "unknown")
        echo "   🔍 App internal DB_HOST: $CONTAINER_DB_HOST"
    fi
    
    # 5. Check if mysql container is actually running
    if ! docker ps | grep -q "mysql-db"; then
        echo "❌ Error: mysql-db container is not running!"
        docker logs mysql-db --tail 20
        exit 1
    fi
    
    echo "   Attempt $i/30: $(echo "$ERROR_MSG" | head -n 1)"
    sleep 2
done
set -e # Re-enable 'stop on error'

if [ "$DB_READY" != true ]; then
    echo "❌ Error: Database never became ready. Check logs above."
    exit 1
fi

# Run Laravel optimizations
echo "⚡ Running Laravel optimizations..."

# 1. Clear everything first to avoid stale cache issues
docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan optimize:clear

if [ "$GENERATE_KEY" = true ]; then
    echo "🔑 Generating Laravel APP_KEY..."
    docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan key:generate
fi

# 2. Ensure permissions and links
docker compose --env-file .env -f docker-compose.temp.yml exec -T app chown -R www-data:www-data storage bootstrap/cache
docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan storage:link --force

# 3. Standard optimizations
docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan migrate --force
docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan config:cache
docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan route:cache
docker compose --env-file .env -f docker-compose.temp.yml exec -T app php artisan view:cache

# 4. Extract built frontend assets
echo "📦 Extracting built frontend assets from container to host..."
# Clear old assets to avoid conflicts
rm -rf public/build
mkdir -p public/build

# Copy assets from the container
docker cp laravel-app:/var/www/public/build/. public/build/

# Ensure host permissions are correct (readable by everyone)
chmod -R 755 public/build
echo "✅ Assets extracted. Contents of public/build:"
ls -R public/build | grep "\.js\|\.css\|\.json" | head -n 5

# 5. Final Reload: Restart app to ensure FPM is clean
echo "🔄 Final reload of the application container..."
docker compose --env-file .env -f docker-compose.temp.yml restart app
# ^ FIX 2: Removed extra 'fi' here

# Cleanup temp file
rm -f docker-compose.temp.yml

echo ""
echo "🔍 Final health check..."
if docker ps | grep -q "nginx-web"; then
    echo "✅ Nginx container is running."
    if curl -s -I -k https://localhost | grep -q "200\|302\|301"; then
        echo "✅ Site is responding locally via HTTPS."
    else
        echo "⚠️  Site is returning an error (likely 502 or 500). Checking logs..."
        echo "--- Last 20 lines of Laravel Application Logs ---"
        docker logs laravel-app --tail 20
        echo "--- Last 20 lines of Nginx Error Logs ---"
        docker logs nginx-web --tail 20
    fi
else
    echo "❌ Error: Nginx container failed to start!"
    docker logs nginx-web --tail 20
fi
echo ""
echo "✅ Deployment complete!"
echo "🌐 Your site should be available at: https://$DOMAIN"
echo ""
echo "📊 Check status with: docker compose ps"
echo "📜 View logs with: docker logs -f laravel-app"
echo ""
echo "💡 Note: Generated config saved as deployment/nginx-$DOMAIN.conf"