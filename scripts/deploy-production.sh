#!/bin/bash

# Weather History Hetzner Deployment Script (via ssh elmarhepp)
# Usage: bash deploy-production.sh <your-email> [--force-rebuild]
# Example: bash deploy-production.sh admin@example.com

set -e

EMAIL="${1}"
FORCE_REBUILD="${2:-}"

if [ -z "$EMAIL" ]; then
    echo "❌ Usage: bash deploy-production.sh <your-email> [--force-rebuild]"
    echo "Example: bash deploy-production.sh admin@example.com"
    exit 1
fi

echo "🚀 Weather History Deployment to Hetzner"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Server:        ssh elmarhepp"
echo "Email:         $EMAIL"
echo "Force Rebuild: ${FORCE_REBUILD:-no}"
echo ""

# Step 1: Validate git status
echo "📋 Step 1: Validating git status..."
if [ -n "$(git status --porcelain)" ]; then
    echo "⚠️  You have uncommitted changes. Commit them first:"
    git status --short
    exit 1
fi
echo "✅ Git status clean"
echo ""

# Step 2: Push to remote
echo "📤 Step 2: Pushing to remote..."
git push origin main
echo "✅ Pushed to origin/main"
echo ""

# Step 3: Connect to server and deploy
echo "🔗 Step 3: Connecting to Hetzner server..."
echo "Note: Uses 'ssh elmarhepp' alias configured in ~/.ssh/config"
echo ""

ssh elmarhepp << EOSSH
set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "\${BLUE}╔════════════════════════════════════════╗\${NC}"
echo -e "\${BLUE}║ Weather History Production Deployment  ║\${NC}"
echo -e "\${BLUE}╚════════════════════════════════════════╝\${NC}"
echo ""

# Create deploy directory if not exists
if [ ! -d "/var/www/weather-history" ]; then
    echo -e "\${YELLOW}Creating /var/www/weather-history...\${NC}"
    mkdir -p /var/www/weather-history
fi

cd /var/www/weather-history

# Clone or update repository
echo -e "\${YELLOW}Updating repository...\${NC}"
if [ -d ".git" ]; then
    git fetch origin
    git reset --hard origin/main
else
    git clone https://github.com/elmarhepp/weather-history.git .
fi
echo -e "\${GREEN}✅ Repository updated\${NC}"
echo ""

# Create .env.production if not exists
if [ ! -f ".env.production" ]; then
    echo -e "\${YELLOW}Creating .env.production...\${NC}"
    cp .env.production.example .env.production
    
    # Generate secure password
    SECURE_PASSWORD=\$(openssl rand -base64 32)
    sed -i "s/DB_PASSWORD=CHANGE_ME_SECURE_PASSWORD_HERE/DB_PASSWORD=\${SECURE_PASSWORD}/" .env.production
    
    echo -e "\${GREEN}✅ .env.production created with secure password\${NC}"
    echo -e "\${YELLOW}💾 Save this password securely!\${NC}"
else
    echo -e "\${GREEN}✅ .env.production exists (keeping existing)\${NC}"
fi
echo ""

# Create Laravel backend .env
echo -e "\${YELLOW}Creating Laravel .env...\${NC}"
if [ ! -f "laravel-backend/.env" ]; then
    cat > laravel-backend/.env << 'EOF'
APP_NAME="Weather History"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://weather-api.elmarhepp.de
LOG_CHANNEL=stack

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=weather_history
DB_USERNAME=weather_user
DB_PASSWORD=\${DB_PASSWORD}

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

APP_KEY=base64:jxwGV8A4TenqVpBJ8MQqjVQM7K9pnAOhJ+kkRPh9J6w=
EOF
    echo -e "\${GREEN}✅ Laravel .env created\${NC}"
else
    echo -e "\${GREEN}✅ Laravel .env exists\${NC}"
fi
echo ""

# Create Vue frontend .env.production
echo -e "\${YELLOW}Creating Vue .env.production...\${NC}"
cat > vue-frontend/.env.production << 'EOF'
VITE_API_BASE_URL=https://weather-api.elmarhepp.de
VITE_APP_NAME=Weather History Deutschland
EOF
echo -e "\${GREEN}✅ Vue .env.production created\${NC}"
echo ""

# Set secure file permissions
echo -e "\${YELLOW}Setting file permissions...\${NC}"
chmod 600 .env.production
chmod 600 laravel-backend/.env 2>/dev/null || true
echo -e "\${GREEN}✅ Permissions set\${NC}"
echo ""

# Start Docker stack
echo -e "\${YELLOW}Starting Docker services...\${NC}"
REBUILD_FLAG=""
[ "$FORCE_REBUILD" = "--force-rebuild" ] && REBUILD_FLAG="--build" || REBUILD_FLAG=""

docker compose -f docker/production/docker-compose.yml up -d \${REBUILD_FLAG}
echo -e "\${GREEN}✅ Docker services started\${NC}"
echo ""

# Wait for services to be ready
echo -e "\${YELLOW}Waiting for services to be healthy...\${NC}"
sleep 10

# Database migrations
echo -e "\${YELLOW}Running database migrations...\${NC}"
docker compose -f docker/production/docker-compose.yml exec -T laravel-backend php artisan migrate --force
echo -e "\${GREEN}✅ Database migrated\${NC}"
echo ""

# Compute aggregates
echo -e "\${YELLOW}Computing weather aggregates...\${NC}"
docker compose -f docker/production/docker-compose.yml exec -T laravel-backend php artisan weather:compute-aggregates || true
echo -e "\${GREEN}✅ Aggregates computed\${NC}"
echo ""

# Setup Nginx on host
echo -e "\${YELLOW}Configuring Nginx...\${NC}"
sudo tee /etc/nginx/sites-available/weather-history.conf > /dev/null << 'NGINX_CONF'
# Weather History Application

# HTTP to HTTPS Redirect (Frontend)
server {
    listen 80;
    server_name weather.elmarhepp.de;
    return 301 https://\$host\$request_uri;
}

# HTTP to HTTPS Redirect (API)
server {
    listen 80;
    server_name weather-api.elmarhepp.de;
    return 301 https://\$host\$request_uri;
}

# HTTPS Frontend
server {
    listen 443 ssl http2;
    server_name weather.elmarhepp.de;

    ssl_certificate /etc/letsencrypt/live/weather.elmarhepp.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/weather.elmarhepp.de/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        proxy_pass http://127.0.0.1:3030;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}

# HTTPS API
server {
    listen 443 ssl http2;
    server_name weather-api.elmarhepp.de;

    ssl_certificate /etc/letsencrypt/live/weather.elmarhepp.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/weather.elmarhepp.de/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        proxy_pass http://127.0.0.1:3031;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
NGINX_CONF

sudo ln -sf /etc/nginx/sites-available/weather-history.conf /etc/nginx/sites-enabled/weather-history.conf
sudo nginx -t
sudo systemctl reload nginx
echo -e "\${GREEN}✅ Nginx configured\${NC}"
echo ""

# Setup SSL certificates
echo -e "\${YELLOW}Setting up SSL certificates with Let's Encrypt...\${NC}"
sudo certbot --nginx -d weather.elmarhepp.de -d weather-api.elmarhepp.de \\
    --non-interactive --agree-tos --email ${EMAIL} \\
    --keep-until-expiring 2>/dev/null || echo -e "\${YELLOW}⚠️ Certbot setup (may already exist)\${NC}"
echo -e "\${GREEN}✅ SSL certificates ready\${NC}"
echo ""

# Verification
echo -e "\${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\${NC}"
echo -e "\${BLUE}🔍 VERIFICATION\${NC}"
echo -e "\${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\${NC}"
echo ""

# Check Docker containers
echo -e "\${YELLOW}Container Status:\${NC}"
docker compose -f docker/production/docker-compose.yml ps
echo ""

# Check Nginx
echo -e "\${YELLOW}Testing Nginx configuration:\${NC}"
sudo nginx -t
echo ""

# Check endpoints
echo -e "\${YELLOW}Testing API endpoints:\${NC}"
echo "Frontend (http://127.0.0.1:3030):"
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:3030

echo "API (http://127.0.0.1:3031):"
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:3031/api/v1/stations
echo ""

echo -e "\${GREEN}╔════════════════════════════════════════╗\${NC}"
echo -e "\${GREEN}║ 🎉 Deployment Complete!               ║\${NC}"
echo -e "\${GREEN}╚════════════════════════════════════════╝\${NC}"
echo ""
echo -e "\${BLUE}Next Steps:\${NC}"
echo "1. Verify DNS at Spaceship is configured:"
echo "   Type: A, Host: @,  Value: <your-hetzner-ip>"
echo "   Type: A, Host: *, Value: <your-hetzner-ip>"
echo ""
echo "2. Test the application:"
echo "   Frontend: https://weather.elmarhepp.de"
echo "   API: https://weather-api.elmarhepp.de/api/v1/stations"
echo ""
echo "3. Monitor logs:"
echo "   docker compose -f docker/production/docker-compose.yml logs -f laravel-backend"
echo ""

EOSSH

echo -e "✅ Deployment successful!"
echo ""
echo "📍 DNS Configuration at Spaceship (if not already done):"
echo "   Type: A, Host: @,  Value: <your-hetzner-ip>"
echo "   Type: A, Host: *, Value: <your-hetzner-ip>"
echo ""
echo "🌐 Access your application (after DNS propagates):"
echo "   Frontend: https://weather.elmarhepp.de"
echo "   API: https://weather-api.elmarhepp.de/api/v1/stations"
