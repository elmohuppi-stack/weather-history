#!/bin/bash

# Weather History Production Health Check Script
# Run this after deployment to verify all systems are operational

set -e

echo "🏥 Weather History Production Health Check"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ERRORS=0
WARNINGS=0

# Helper functions
check_pass() {
    echo -e "${GREEN}✅${NC} $1"
}

check_fail() {
    echo -e "${RED}❌${NC} $1"
    ((ERRORS++))
}

check_warn() {
    echo -e "${YELLOW}⚠️${NC}  $1"
    ((WARNINGS++))
}

# 1. Docker Container Status
echo -e "${BLUE}1. Docker Container Status${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

docker ps --filter "name=weather" --format "table {{.Names}}\t{{.Status}}" | while read line; do
    if [ -n "$line" ]; then
        if echo "$line" | grep -q "Up"; then
            check_pass "$line"
        else
            check_fail "$line"
        fi
    fi
done
echo ""

# 2. Port Binding Status
echo -e "${BLUE}2. Port Binding Status${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if netstat -tlnp 2>/dev/null | grep -q ":3030"; then
    check_pass "Frontend listening on 127.0.0.1:3030"
else
    check_fail "Frontend NOT listening on 127.0.0.1:3030"
fi

if netstat -tlnp 2>/dev/null | grep -q ":3031"; then
    check_pass "API listening on 127.0.0.1:3031"
else
    check_fail "API NOT listening on 127.0.0.1:3031"
fi

if netstat -tlnp 2>/dev/null | grep -q ":5432"; then
    check_pass "PostgreSQL listening on 127.0.0.1:5432"
else
    check_fail "PostgreSQL NOT listening on 127.0.0.1:5432"
fi

if netstat -tlnp 2>/dev/null | grep -q ":6379"; then
    check_pass "Redis listening on 127.0.0.1:6379"
else
    check_fail "Redis NOT listening on 127.0.0.1:6379"
fi
echo ""

# 3. HTTP Endpoints (local)
echo -e "${BLUE}3. HTTP Endpoints (Local)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Frontend
FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:3030 2>/dev/null || echo "000")
if [ "$FRONTEND_STATUS" = "200" ] || [ "$FRONTEND_STATUS" = "301" ] || [ "$FRONTEND_STATUS" = "302" ]; then
    check_pass "Frontend responding (HTTP $FRONTEND_STATUS)"
else
    check_fail "Frontend not responding properly (HTTP $FRONTEND_STATUS)"
fi

# API Stations endpoint
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:3031/api/v1/stations 2>/dev/null || echo "000")
if [ "$API_STATUS" = "200" ]; then
    check_pass "API /stations endpoint responding (HTTP $API_STATUS)"
else
    check_fail "API /stations endpoint not responding (HTTP $API_STATUS)"
fi

# API Health check (if implemented)
HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:3031/health 2>/dev/null || echo "000")
if [ "$HEALTH_STATUS" = "200" ]; then
    check_pass "API health endpoint responding (HTTP $HEALTH_STATUS)"
elif [ "$HEALTH_STATUS" = "404" ]; then
    check_warn "API health endpoint not implemented (HTTP 404)"
else
    check_fail "API health check failed (HTTP $HEALTH_STATUS)"
fi
echo ""

# 4. Database Status
echo -e "${BLUE}4. Database Status${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check database connection from Laravel
DB_CHECK=$(docker compose -f docker/production/docker-compose.yml exec -T laravel-backend php artisan db:monitor 2>&1 | head -1 || echo "FAILED")
if echo "$DB_CHECK" | grep -q "✓"; then
    check_pass "Database connection healthy"
elif echo "$DB_CHECK" | grep -q "Database"; then
    check_pass "Database is connected"
else
    # Try alternative check
    STATION_COUNT=$(curl -s http://127.0.0.1:3031/api/v1/stations | grep -o '"id"' | wc -l 2>/dev/null || echo "0")
    if [ "$STATION_COUNT" -gt "0" ]; then
        check_pass "Database has $STATION_COUNT stations"
    else
        check_fail "Cannot verify database connection"
    fi
fi

# Check tables exist
TABLE_CHECK=$(docker compose -f docker/production/docker-compose.yml exec -T postgres psql -U weather_user -d weather_history -c "\dt" 2>&1 | grep -c "stations" || echo "0")
if [ "$TABLE_CHECK" -gt "0" ]; then
    check_pass "Database tables exist"
else
    check_fail "Database tables not found"
fi
echo ""

# 5. Nginx Configuration
echo -e "${BLUE}5. Nginx Configuration${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if sudo nginx -t 2>/dev/null | grep -q "successful"; then
    check_pass "Nginx configuration is valid"
else
    check_fail "Nginx configuration has errors"
fi

if [ -L "/etc/nginx/sites-enabled/weather-history.conf" ]; then
    check_pass "Weather History Nginx site is enabled"
else
    check_warn "Weather History Nginx site not properly symlinked"
fi
echo ""

# 6. SSL Certificates
echo -e "${BLUE}6. SSL Certificates${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if sudo ls -la /etc/letsencrypt/live/weather.elmarhepp.de/ >/dev/null 2>&1; then
    CERT_DATE=$(sudo openssl x509 -in /etc/letsencrypt/live/weather.elmarhepp.de/cert.pem -noout -enddate 2>/dev/null | cut -d= -f2)
    check_pass "SSL certificate installed (expires: $CERT_DATE)"
else
    check_warn "SSL certificate not found (may need setup)"
fi

if sudo certbot renew --dry-run >/dev/null 2>&1; then
    check_pass "Let's Encrypt auto-renewal is configured"
else
    check_warn "Let's Encrypt auto-renewal may not be working"
fi
echo ""

# 7. HTTPS Endpoints (if DNS is configured)
echo -e "${BLUE}7. HTTPS Endpoints (if DNS configured)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

FRONTEND_HTTPS=$(curl -s -o /dev/null -w "%{http_code}" https://weather.elmarhepp.de 2>/dev/null || echo "000")
if [ "$FRONTEND_HTTPS" = "200" ] || [ "$FRONTEND_HTTPS" = "301" ] || [ "$FRONTEND_HTTPS" = "302" ]; then
    check_pass "Frontend HTTPS responding (HTTP $FRONTEND_HTTPS)"
elif [ "$FRONTEND_HTTPS" = "000" ]; then
    check_warn "HTTPS frontend check failed (DNS may not be configured yet)"
else
    check_warn "Frontend HTTPS returned unexpected status (HTTP $FRONTEND_HTTPS)"
fi

API_HTTPS=$(curl -s -o /dev/null -w "%{http_code}" https://weather-api.elmarhepp.de/api/v1/stations 2>/dev/null || echo "000")
if [ "$API_HTTPS" = "200" ]; then
    check_pass "API HTTPS responding (HTTP $API_HTTPS)"
elif [ "$API_HTTPS" = "000" ]; then
    check_warn "HTTPS API check failed (DNS may not be configured yet)"
else
    check_warn "API HTTPS returned unexpected status (HTTP $API_HTTPS)"
fi
echo ""

# 8. Disk Space
echo -e "${BLUE}8. Disk Space${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

DISK_USAGE=$(df /var/www/weather-history | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -lt "80" ]; then
    check_pass "Disk space healthy ($DISK_USAGE% used)"
elif [ "$DISK_USAGE" -lt "95" ]; then
    check_warn "Disk space warning ($DISK_USAGE% used)"
else
    check_fail "Disk space critical ($DISK_USAGE% used)"
fi

DB_SIZE=$(docker compose -f docker/production/docker-compose.yml exec -T postgres psql -U weather_user -d weather_history -c "SELECT pg_size_pretty(pg_database_size('weather_history'));" 2>/dev/null | tail -1 || echo "unknown")
check_pass "Database size: $DB_SIZE"
echo ""

# 9. Application Logs
echo -e "${BLUE}9. Application Logs (last 5 errors)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

LOG_ERRORS=$(docker compose -f docker/production/docker-compose.yml logs laravel-backend 2>/dev/null | grep -i "error\|exception\|fatal" | tail -3 || echo "No recent errors")
if echo "$LOG_ERRORS" | grep -q "error\|exception"; then
    check_warn "Recent errors in logs:"
    echo "$LOG_ERRORS" | sed 's/^/  /'
else
    check_pass "No recent errors in logs"
fi
echo ""

# Summary
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}SUMMARY${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

if [ "$ERRORS" -eq "0" ] && [ "$WARNINGS" -eq "0" ]; then
    echo -e "${GREEN}🎉 All systems operational!${NC}"
    exit 0
elif [ "$ERRORS" -eq "0" ]; then
    echo -e "${YELLOW}⚠️  System operational with $WARNINGS warning(s)${NC}"
    exit 0
else
    echo -e "${RED}❌ $ERRORS error(s) detected, $WARNINGS warning(s)${NC}"
    exit 1
fi
