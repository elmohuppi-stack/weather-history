# Weather History Deutschland - Hetzner Production Deployment

**Stand:** 19. April 2026  
**Status:** ✅ Production-Ready

---

## 📋 App-Konfiguration

| Variable          | Wert                       |
| ----------------- | -------------------------- |
| `APP_SLUG`        | `weather-history`          |
| `FRONTEND_DOMAIN` | `weather.elmarhepp.de`     |
| `API_DOMAIN`      | `weather-api.elmarhepp.de` |
| `WEB_PORT`        | `3030`                     |
| `API_PORT`        | `3031`                     |
| `DEPLOY_PATH`     | `/var/www/weather-history` |

---

## 🚀 Schritt-für-Schritt Deployment

### Phase 1: Vorbereitung auf der Workstation (lokal)

#### 1.1 Alle Änderungen committen

```bash
cd /Users/elmarhepp/workspace/weather-history
git add -A
git commit -m "Prepare for Hetzner production deployment"
git push origin main
```

#### 1.2 Deployment-Dateien vorbereiten

```bash
# Docker Compose für Production (wird unten bereitgestellt)
# Environment-Dateien werden in Phase 2 auf dem Server erstellt
```

### Phase 2: Vorbereitung auf dem Hetzner-Server

#### 2.1 Mit SSH verbinden

```bash
ssh root@<hetzner-ip>
```

#### 2.2 Verzeichnis erstellen

```bash
mkdir -p /var/www/weather-history
cd /var/www/weather-history
```

#### 2.3 Repository klonen

```bash
git clone https://github.com/<username>/weather-history.git .
# oder falls SSH konfiguriert:
git clone git@github.com:<username>/weather-history.git .
```

#### 2.4 Environment-Dateien erstellen

**Root `.env.production`:**

```bash
cat > .env.production << 'EOF'
APP_ENV=production
APP_DEBUG=false
APP_NAME="Weather History Deutschland"
APP_DOMAIN=weather.elmarhepp.de
API_DOMAIN=weather-api.elmarhepp.de
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=weather_history
DB_USERNAME=weather_user
DB_PASSWORD=<SICHERES_PASSWORT_GENERIEREN>
REDIS_HOST=redis
REDIS_PORT=6379
EOF
```

**Laravel Backend `.env` (in `laravel-backend/.env`):**

```bash
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
DB_PASSWORD=<SICHERES_PASSWORT_GENERIEREN>
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
EOF
```

**Frontend `.env.production` (in `vue-frontend`):**

```bash
cat > vue-frontend/.env.production << 'EOF'
VITE_API_BASE_URL=https://weather-api.elmarhepp.de
VITE_APP_NAME=Weather History Deutschland
EOF
```

#### 2.5 Secrets sichern

```bash
# Dateirechte setzen (nur root lesbar)
chmod 600 .env.production
chmod 600 laravel-backend/.env
chmod 600 vue-frontend/.env.production
```

### Phase 3: Docker-Compose Stack starten

#### 3.1 Produktions-Compose verwenden

```bash
cd /var/www/weather-history
docker compose -f docker/production/docker-compose.yml up -d --build
```

#### 3.2 Basis-Initialisierung durchführen

```bash
# Datenbank migrieren
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan migrate --force

# Datenbank seeden (optional, für Test-Daten)
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan db:seed

# Wetterdaten aggregieren
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan weather:compute-aggregates
```

#### 3.3 Stack-Status prüfen

```bash
docker compose -f docker/production/docker-compose.yml ps
# Alle Container sollten "Up" sein
```

### Phase 4: Nginx auf Host-System konfigurieren

#### 4.1 Nginx-Konfiguration erstellen

```bash
sudo tee /etc/nginx/sites-available/weather-history.conf > /dev/null << 'EOF'
# HTTP → HTTPS Redirect (Frontend)
server {
    listen 80;
    server_name weather.elmarhepp.de;
    return 301 https://$host$request_uri;
}

# HTTP → HTTPS Redirect (API)
server {
    listen 80;
    server_name weather-api.elmarhepp.de;
    return 301 https://$host$request_uri;
}

# HTTPS Frontend (Vue)
server {
    listen 443 ssl http2;
    server_name weather.elmarhepp.de;

    ssl_certificate /etc/letsencrypt/live/weather.elmarhepp.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/weather.elmarhepp.de/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        proxy_pass http://127.0.0.1:3030;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Connection "upgrade";
        proxy_set_header Upgrade $http_upgrade;
        proxy_buffering off;
    }
}

# HTTPS API (Laravel)
server {
    listen 443 ssl http2;
    server_name weather-api.elmarhepp.de;

    ssl_certificate /etc/letsencrypt/live/weather.elmarhepp.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/weather.elmarhepp.de/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        proxy_pass http://127.0.0.1:3031;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_buffering off;
    }
}
EOF
```

#### 4.2 Nginx-Konfiguration aktivieren

```bash
sudo ln -sf /etc/nginx/sites-available/weather-history.conf /etc/nginx/sites-enabled/weather-history.conf

# Testen
sudo nginx -t

# Neu laden
sudo systemctl reload nginx
```

### Phase 5: SSL-Zertifikate mit Certbot

#### 5.1 Let's Encrypt Zertifikate generieren

```bash
sudo certbot --nginx -d weather.elmarhepp.de -d weather-api.elmarhepp.de --agree-tos --email <your-email@domain.com>
```

#### 5.2 Auto-Renewal konfigurieren

```bash
# Cron-Job für Certbot prüfen
sudo systemctl status certbot.timer

# Oder manuell testen:
sudo certbot renew --dry-run
```

### Phase 6: DNS bei Spaceship konfigurieren

Stelle sicher, dass deine DNS-Einträge bei Spaceship so gesetzt sind:

```
Typ    Host    Wert
A      @       <hetzner-ip>
A      *       <hetzner-ip>
```

Dies stellt sicher, dass `weather.elmarhepp.de` und `weather-api.elmarhepp.de` auf deinen Hetzner-Server zeigen.

---

## ✅ Verifikation nach dem Deployment

### 1. Frontend erreichbar?

```bash
curl -I https://weather.elmarhepp.de/
# Erwartet: HTTP/2 200 oder 301/302 (Redirect ok)
```

### 2. API antwortet?

```bash
curl -I https://weather-api.elmarhepp.de/api/v1/stations
# Erwartet: HTTP/2 200
```

### 3. HTTPS funktioniert?

```bash
curl -I https://weather.elmarhepp.de/ | grep "Strict-Transport-Security"
# Erwartet: HSTS Header present
```

### 4. Datenbank verbunden?

```bash
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan tinker << 'EOF'
use App\Models\Station;
echo Station::count() . " Stations loaded\n";
EOF
```

### 5. Frontend-API kommunikation?

Öffne `https://weather.elmarhepp.de` im Browser und prüfe:

- Dashboard lädt
- Stationsliste sichtbar
- Keine Netzwerkfehler in Browser DevTools (F12)

---

## 🔄 Updates & Wartung

### Automatische Updates (täglich)

```bash
# Cronjob für DWD-Datenaktualisierung
cat > /etc/cron.d/weather-history << 'EOF'
# Weather History Daily Updates
0 3 * * * root cd /var/www/weather-history && docker compose -f docker/production/docker-compose.yml exec -T python-etl python scripts/dwd_importer.py >> /var/log/weather-history-import.log 2>&1
0 4 * * * root cd /var/www/weather-history && docker compose -f docker/production/docker-compose.yml exec -T laravel-backend php artisan weather:compute-aggregates >> /var/log/weather-history-aggregate.log 2>&1
EOF
```

### Manueller Update

```bash
cd /var/www/weather-history
git pull origin main
docker compose -f docker/production/docker-compose.yml up -d --build
```

### Datenbank-Backup

```bash
# Tägliche Backups (3:30 UTC)
0 3 * * * root docker compose -f /var/www/weather-history/docker/production/docker-compose.yml exec -T postgres pg_dump -U weather_user weather_history | gzip > /var/backups/weather-history-$(date +\%Y\%m\%d).sql.gz
```

---

## 🆘 Troubleshooting

### Logs ansehen

```bash
# Laravel Backend
docker compose -f docker/production/docker-compose.yml logs laravel-backend --tail 100

# Vue Frontend Build
docker compose -f docker/production/docker-compose.yml logs vue-frontend --tail 50

# Nginx (Host)
sudo journalctl -u nginx -n 50

# Certbot
sudo journalctl -u certbot -n 20
```

### Container neu starten

```bash
# Single service
docker compose -f docker/production/docker-compose.yml restart laravel-backend

# All services
docker compose -f docker/production/docker-compose.yml restart
```

### Datenbank zurücksetzen

```bash
# ⚠️ GEFÄHRLICH - Nur in Notfall!
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan migrate:fresh --force --seed
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan weather:compute-aggregates
```

---

## 📊 Monitoring

### Container Health prüfen

```bash
watch -n 5 'docker compose -f docker/production/docker-compose.yml ps'
```

### Disk Space

```bash
df -h /var/www/weather-history
du -sh /var/www/weather-history/*
```

### Datenbankgröße

```bash
docker compose -f docker/production/docker-compose.yml exec postgres psql -U weather_user -d weather_history -c "SELECT pg_size_pretty(pg_database_size('weather_history'));"
```

### CPU/Memory

```bash
docker stats
```

---

## 🔐 Sicherheit

### Firewall-Konfiguration

```bash
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw enable
```

### Docker-Sicherheit

```bash
# Container nicht als root ausführen (sollte bereits in Dockerfile implementiert sein)
# Secrets nicht in Umgebungsvariablen hardcoden
# Regelmäßig Docker Images updaten
```

### Let's Encrypt Renewal

```bash
# Auto-Renewal sollte bereits via systemd laufen
sudo systemctl status certbot.timer

# Manuell testen
sudo certbot renew --dry-run
```

---

## 📞 Support

Bei Problemen:

1. Logs prüfen (siehe Troubleshooting)
2. Container Status prüfen (`docker ps`)
3. Netzwerk testen (`curl`, Browser)
4. Datenbankverbindung prüfen
5. SSL-Zertifikate validieren (`curl -I`)

---

**Status: ✅ Production Ready**
**Letzte Aktualisierung: 19. April 2026**
**Nächster Check: Nach dem initialen Deployment auf Hetzner**
