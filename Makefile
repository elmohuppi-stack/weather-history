# Weather History DWD - Makefile for local development
# Usage: make [target]

.PHONY: help start stop restart build clean logs db-shell frontend backend etl test dev prod-sim health install update start-docker stop-docker start-backend stop-backend start-frontend stop-frontend

# Default target
help:
	@echo "🌤️ Weather History DWD - Development Commands"
	@echo "=============================================="
	@echo ""
	@echo "📦 Docker Commands:"
	@echo "  make start        - Start ALL services (docker + backend + frontend)"
	@echo "  make stop         - Stop ALL services (docker + backend + frontend)"
	@echo "  make restart      - Restart all services"
	@echo "  make build        - Build Docker images"
	@echo "  make clean        - Stop services and remove volumes"
	@echo "  make logs         - Show Docker logs (follow)"
	@echo "  make db-shell     - Open PostgreSQL shell"
	@echo ""
	@echo "🔧 Service Commands:"
	@echo "  make frontend     - Start Vue.js frontend development server"
	@echo "  make backend      - Start Laravel backend development server"
	@echo "  make etl          - Run Python ETL import (sample data)"
	@echo "  make dev          - Quick start: start all services (docker + backend + frontend)"
	@echo "  make prod-sim     - Production simulation (build and start all services)"
	@echo ""
	@echo "🎯 Individual Service Control:"
	@echo "  make start-docker - Start only Docker services"
	@echo "  make stop-docker  - Stop only Docker services"
	@echo "  make start-backend - Start only backend server"
	@echo "  make stop-backend  - Stop backend server"
	@echo "  make start-frontend - Start only frontend server"
	@echo "  make stop-frontend  - Stop frontend server"
	@echo ""
	@echo "🧪 Test Commands:"
	@echo "  make test         - Run all tests"
	@echo "  make test-frontend - Run Vue.js tests"
	@echo "  make test-backend  - Run Laravel tests"
	@echo ""
	@echo "📊 Database Commands:"
	@echo "  make db-init      - Initialize database schema"
	@echo "  make db-reset     - Reset database (drop & recreate)"
	@echo "  make db-backup    - Create database backup"
	@echo "  make db-restore   - Restore database from backup"
	@echo ""
	@echo "🔍 Utility Commands:"
	@echo "  make health       - Check health of all services"
	@echo "  make install      - Install all dependencies (PHP, Node.js, Python)"
	@echo "  make update       - Update all dependencies"
	@echo ""
	@echo "🌐 URLs:"
	@echo "  Frontend:    http://localhost:3000"
	@echo "  Backend API: http://localhost:8000"
	@echo "  Adminer:     http://localhost:8080"
	@echo "  PostgreSQL:  localhost:5432"
	@echo ""

# Docker commands
start: start-docker start-backend start-frontend
	@echo "🚀 All services started!"
	@echo "🌐 Frontend: http://localhost:3000"
	@echo "🌐 Backend:  http://localhost:8000"
	@echo "🌐 Adminer:  http://localhost:8080"
	@echo "🐘 PostgreSQL: localhost:5432"
	@echo "🔴 Redis:    localhost:6379"

stop: stop-docker stop-backend stop-frontend
	@echo "🛑 All services stopped!"

restart: stop start

build:
	@echo "🔨 Building Docker images..."
	cd docker/development && docker-compose build

clean:
	@echo "🧹 Cleaning up Docker resources..."
	cd docker/development && docker-compose down -v
	@echo "✅ All containers and volumes removed"

logs:
	@echo "📋 Showing Docker logs (Ctrl+C to exit)..."
	cd docker/development && docker-compose logs -f

db-shell:
	@echo "🐘 Opening PostgreSQL shell..."
	cd docker/development && docker-compose exec postgres psql -U weather_user -d weather_history

# Service commands
frontend:
	@echo "🎨 Starting Vue.js frontend..."
	cd vue-frontend && npm run dev

backend:
	@echo "⚙️  Starting Laravel backend..."
	@echo "📦 Installing dependencies..."
	cd laravel-backend && composer install
	@echo "🔑 Generating application key..."
	cd laravel-backend && php artisan key:generate
	@echo "🗄️  Running migrations..."
	cd laravel-backend && php artisan migrate
	@echo "🚀 Starting development server..."
	cd laravel-backend && php artisan serve

# Individual service control commands
start-docker:
	@echo "🐳 Starting Docker services..."
	cd docker/development && docker-compose up -d postgres redis adminer
	@echo "✅ Docker services started:"
	@echo "  • PostgreSQL: localhost:5432"
	@echo "  • Redis:      localhost:6379"
	@echo "  • Adminer:    http://localhost:8080"

stop-docker:
	@echo "🐳 Stopping Docker services..."
	cd docker/development && docker-compose down
	@echo "✅ Docker services stopped"

start-backend: backend
	@echo "✅ Backend started at http://localhost:8000"

stop-backend:
	@echo "🛑 Stopping backend..."
	@if pkill -f "php -S 127.0.0.1:8000"; then \
		echo "✅ Backend stopped"; \
	else \
		echo "ℹ️  Backend was not running"; \
	fi

start-frontend: frontend
	@echo "✅ Frontend started at http://localhost:3000"

stop-frontend:
	@echo "🛑 Stopping frontend..."
	@if pkill -f "npm run dev"; then \
		echo "✅ Frontend stopped"; \
	else \
		echo "ℹ️  Frontend was not running"; \
	fi

etl:
	@echo "🐍 Running Python ETL import..."
	@echo "📦 Installing Python dependencies..."
	cd etl-python && pip install -r requirements.txt
	@echo "📊 Importing sample data..."
	cd etl-python && python3 scripts/dwd_importer.py --init-db --import-all
	@echo "✅ ETL import completed"

# Test commands
test: test-backend test-frontend

test-frontend:
	@echo "🧪 Running Vue.js tests..."
	cd vue-frontend && npm test

test-backend:
	@echo "🧪 Running Laravel tests..."
	cd laravel-backend && php artisan test

# Database commands
db-init:
	@echo "🗄️  Initializing database schema..."
	cd docker/development && docker-compose exec -T postgres psql -U weather_user -d weather_history -f /docker-entrypoint-initdb.d/01-init-schema.sql
	@echo "✅ Database schema initialized"

db-reset:
	@echo "🔄 Resetting database..."
	@read -p "Are you sure? This will delete all data! (y/N): " confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		echo "Dropping and recreating database..."; \
		cd docker/development && docker-compose down -v; \
		docker-compose up -d postgres; \
		sleep 10; \
		docker-compose exec -T postgres psql -U weather_user -d weather_history -f /docker-entrypoint-initdb.d/01-init-schema.sql; \
		echo "✅ Database reset complete"; \
	else \
		echo "❌ Database reset cancelled"; \
	fi

db-backup:
	@echo "💾 Creating database backup..."
	@mkdir -p backups
	cd docker/development && docker-compose exec -T postgres pg_dump -U weather_user -d weather_history > ../backups/weather_history_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup saved to backups/"

db-restore:
	@echo "🔄 Restoring database from backup..."
	@if [ -z "$(file)" ]; then \
		echo "Usage: make db-restore file=backups/filename.sql"; \
		exit 1; \
	fi
	@if [ ! -f "$(file)" ]; then \
		echo "❌ File $(file) not found"; \
		exit 1; \
	fi
	@read -p "Are you sure? This will overwrite current data! (y/N): " confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		echo "Restoring from $(file)..."; \
		cd docker/development && docker-compose exec -T postgres psql -U weather_user -d weather_history < ../../$(file); \
		echo "✅ Database restored from $(file)"; \
	else \
		echo "❌ Database restore cancelled"; \
	fi

# Quick start for development
dev: start
	@echo "🚀 Development environment started!"
	@echo "🌐 Frontend: http://localhost:3000"
	@echo "🌐 Backend:  http://localhost:8000"
	@echo "🌐 Adminer:  http://localhost:8080"

# Production simulation
prod-sim:
	@echo "🏭 Starting production simulation..."
	@echo "📦 Building all services..."
	make build
	@echo "🚀 Starting all services..."
	cd docker/development && docker-compose up -d
	@echo "✅ Production simulation started"
	@echo "🌐 Frontend: http://localhost:3000"
	@echo "🌐 Backend:  http://localhost:8000"
	@echo "🌐 Adminer:  http://localhost:8080"

# Health check
health:
	@echo "🏥 Checking service health..."
	@echo "📊 Docker containers:"
	cd docker/development && docker-compose ps
	@echo ""
	@echo "🔗 Checking connections..."
	@echo "• PostgreSQL: $$(cd docker/development && docker-compose exec -T postgres pg_isready -U weather_user -d weather_history && echo "✅" || echo "❌")"
	@echo "• Redis: $$(cd docker/development && docker-compose exec redis redis-cli ping 2>/dev/null | grep -q PONG && echo "✅" || echo "❌")"
	@echo "• Frontend: $$(curl -s -o /dev/null -w "%{http_code}" http://localhost:3000 2>/dev/null | grep -q 200 && echo "✅" || echo "❌")"
	@echo "• Backend: $$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 2>/dev/null | grep -q 200 && echo "✅" || echo "❌")"

# Install dependencies
install:
	@echo "📦 Installing all dependencies..."
	@echo "🐘 Backend (PHP)..."
	cd laravel-backend && composer install
	@echo "🎨 Frontend (Node.js)..."
	cd vue-frontend && npm install
	@echo "🐍 ETL (Python)..."
	cd etl-python && pip install -r requirements.txt
	@echo "✅ All dependencies installed"

# Update dependencies
update:
	@echo "🔄 Updating all dependencies..."
	@echo "🐘 Backend (PHP)..."
	cd laravel-backend && composer update
	@echo "🎨 Frontend (Node.js)..."
	cd vue-frontend && npm update
	@echo "🐍 ETL (Python)..."
	cd etl-python && pip install --upgrade -r requirements.txt
	@echo "✅ All dependencies updated"