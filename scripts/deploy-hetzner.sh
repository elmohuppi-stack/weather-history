#!/bin/bash

# Weather History DWD - Hetzner Deployment Script
# Usage: ./scripts/deploy-hetzner.sh [environment]

set -e

# Configuration
ENVIRONMENT=${1:-production}
PROJECT_NAME="weather-history"
DEPLOY_DIR="/var/www/${PROJECT_NAME}"
DOCKER_COMPOSE_FILE="docker/production/docker-compose.yml"
ENV_FILE="docker/production/.env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root"
        exit 1
    fi
}

# Check Docker installation
check_docker() {
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    log_info "Docker and Docker Compose are installed"
}

# Check environment file
check_env_file() {
    if [[ ! -f "$ENV_FILE" ]]; then
        log_warn "Environment file $ENV_FILE not found"
        log_info "Creating from example file..."
        cp "docker/production/.env.example" "$ENV_FILE"
        log_warn "Please edit $ENV_FILE and set the correct values before deployment"
        exit 1
    fi
    
    # Check for required variables
    if grep -q "your_secure_database_password_here" "$ENV_FILE"; then
        log_error "Please update the database password in $ENV_FILE"
        exit 1
    fi
    
    if grep -q "generate_with_php_artisan_key_generate" "$ENV_FILE"; then
        log_error "Please generate an application key in $ENV_FILE"
        log_info "You can generate one with: php artisan key:generate --show"
        exit 1
    fi
    
    log_info "Environment file check passed"
}

# Create deployment directory
setup_deployment_dir() {
    log_info "Setting up deployment directory: $DEPLOY_DIR"
    
    if [[ ! -d "$DEPLOY_DIR" ]]; then
        mkdir -p "$DEPLOY_DIR"
        log_info "Created deployment directory"
    fi
    
    # Copy project files
    log_info "Copying project files to deployment directory..."
    rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' \
        --exclude='*.log' --exclude='storage/*' --exclude='.env' \
        . "$DEPLOY_DIR/"
    
    # Copy environment file
    if [[ -f "$ENV_FILE" ]]; then
        cp "$ENV_FILE" "$DEPLOY_DIR/docker/production/.env"
        log_info "Copied environment file"
    fi
    
    # Set permissions
    chown -R www-data:www-data "$DEPLOY_DIR"
    chmod -R 755 "$DEPLOY_DIR"
    
    log_info "Deployment directory setup complete"
}

# Build and start services
deploy_services() {
    log_info "Deploying services..."
    
    cd "$DEPLOY_DIR"
    
    # Stop existing services
    log_info "Stopping existing services..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" down || true
    
    # Pull latest images
    log_info "Pulling latest images..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" pull
    
    # Build services
    log_info "Building services..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" build
    
    # Start services
    log_info "Starting services..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" up -d
    
    # Wait for services to be healthy
    log_info "Waiting for services to be healthy..."
    sleep 30
    
    # Run database migrations
    log_info "Running database migrations..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec laravel-backend php artisan migrate --force
    
    # Clear caches
    log_info "Clearing caches..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec laravel-backend php artisan optimize:clear
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec laravel-backend php artisan config:cache
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec laravel-backend php artisan route:cache
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec laravel-backend php artisan view:cache
    
    log_info "Services deployed successfully"
}

# Check service health
check_health() {
    log_info "Checking service health..."
    
    cd "$DEPLOY_DIR"
    
    # Check Docker containers
    log_info "Docker containers status:"
    docker-compose -f "$DOCKER_COMPOSE_FILE" ps
    
    # Check service health endpoints
    log_info "Checking health endpoints..."
    
    # Frontend health
    if curl -s -f "http://localhost:3031/health" > /dev/null; then
        log_info "Frontend: ✓ Healthy"
    else
        log_error "Frontend: ✗ Unhealthy"
    fi
    
    # Backend health
    if curl -s -f "http://localhost:3032/health" > /dev/null; then
        log_info "Backend: ✓ Healthy"
    else
        log_error "Backend: ✗ Unhealthy"
    fi
    
    # Database health
    if docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T postgres pg_isready -U weather_user -d weather_history > /dev/null; then
        log_info "Database: ✓ Healthy"
    else
        log_error "Database: ✗ Unhealthy"
    fi
    
    # Redis health
    if docker-compose -f "$DOCKER_COMPOSE_FILE" exec redis redis-cli ping > /dev/null; then
        log_info "Redis: ✓ Healthy"
    else
        log_error "Redis: ✗ Unhealthy"
    fi
}

# Create systemd service (optional)
create_systemd_service() {
    log_info "Creating systemd service..."
    
    SERVICE_FILE="/etc/systemd/system/${PROJECT_NAME}.service"
    
    cat > "$SERVICE_FILE" << EOF
[Unit]
Description=Weather History DWD Application
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=$DEPLOY_DIR
ExecStart=/usr/bin/docker-compose -f $DOCKER_COMPOSE_FILE up -d
ExecStop=/usr/bin/docker-compose -f $DOCKER_COMPOSE_FILE down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
EOF
    
    systemctl daemon-reload
    systemctl enable "${PROJECT_NAME}.service"
    
    log_info "Systemd service created and enabled"
}

# Setup log rotation
setup_log_rotation() {
    log_info "Setting up log rotation..."
    
    LOGROTATE_FILE="/etc/logrotate.d/${PROJECT_NAME}"
    
    cat > "$LOGROTATE_FILE" << EOF
$DEPLOY_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 640 www-data www-data
    sharedscripts
    postrotate
        docker-compose -f $DEPLOY_DIR/$DOCKER_COMPOSE_FILE exec laravel-backend php artisan log:clear > /dev/null 2>&1 || true
    endscript
}
EOF
    
    log_info "Log rotation configured"
}

# Main deployment function
main() {
    log_info "Starting Hetzner deployment for $ENVIRONMENT environment"
    
    check_root
    check_docker
    check_env_file
    setup_deployment_dir
    deploy_services
    check_health
    create_systemd_service
    setup_log_rotation
    
    log_info "Deployment completed successfully!"
    log_info ""
    log_info "Application URLs:"
    log_info "  Frontend: http://localhost:3031"
    log_info "  Backend API: http://localhost:3032"
    log_info "  Adminer: http://localhost:8080"
    log_info ""
    log_info "To view logs: docker-compose -f $DEPLOY_DIR/$DOCKER_COMPOSE_FILE logs -f"
    log_info "To restart: systemctl restart ${PROJECT_NAME}"
}

# Run main function
main "$@"