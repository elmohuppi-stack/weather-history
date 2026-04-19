#!/bin/bash

# Weather History DWD - Development Start Script
# Startet die gesamte Entwicklungsumgebung mit Docker Compose

set -e

echo "🌤️ Weather History DWD - Development Environment"
echo "================================================"

# Prüfe ob Docker läuft
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker ist nicht gestartet. Bitte starte Docker Desktop."
    exit 1
fi

# Prüfe ob Docker Compose verfügbar ist
if ! command -v docker-compose &> /dev/null; then
    echo "⚠️  docker-compose nicht gefunden. Verwende 'docker compose'..."
    DOCKER_COMPOSE="docker compose"
else
    DOCKER_COMPOSE="docker-compose"
fi

# Wechsel ins Docker-Verzeichnis
cd "$(dirname "$0")/../docker/development"

echo ""
echo "📦 Starte Docker Container..."
echo "---------------------------"

# Container starten
$DOCKER_COMPOSE up -d

# Warte auf Container
echo ""
echo "⏳ Warte auf Container-Start..."
sleep 10

# Prüfe Container-Status
echo ""
echo "🔍 Container-Status:"
echo "-------------------"
$DOCKER_COMPOSE ps

echo ""
echo "🌐 Services verfügbar unter:"
echo "---------------------------"
echo "• Frontend (Vue.js):    http://localhost:3000"
echo "• Backend API (Laravel): http://localhost:8000"
echo "• Adminer (DB GUI):     http://localhost:8080"
echo "• PostgreSQL:           localhost:5432 (weather_history)"
echo "• Redis:                localhost:6379"

echo ""
echo "📊 Datenbank einrichten:"
echo "----------------------"
echo "1. Datenbank-Schema erstellen:"
echo "   docker-compose exec -T postgres psql -U weather_user -d weather_history -f /docker-entrypoint-initdb.d/01-init-schema.sql"

echo ""
echo "2. Laravel Migrationen im Container ausführen:"
echo "   docker-compose exec laravel-backend php artisan migrate --force"

echo ""
echo "3. Python ETL im Container ausführen:"
echo "   docker-compose run --rm python-etl python scripts/dwd_importer.py --init-db --import-all"

echo ""
echo "🔧 Nützliche Befehle:"
echo "-------------------"
echo "• Logs anzeigen:          docker-compose logs -f"
echo "• Container stoppen:      docker-compose down"
echo "• In Laravel Container:   docker-compose exec laravel-backend bash"
echo "• In PostgreSQL:          docker-compose exec postgres psql -U weather_user -d weather_history"
echo "• Datenbank-Backup:       docker-compose exec postgres pg_dump -U weather_user weather_history > backup.sql"

echo ""
echo "✅ Entwicklungsumgebung ist bereit!"
echo "ℹ️  Öffne http://localhost:3000 im Browser"

# Logs anzeigen (optional)
if [[ "$1" == "--logs" ]]; then
    echo ""
    echo "📋 Starte Log-Ansicht..."
    $DOCKER_COMPOSE logs -f
