# 🚀 Hetzner Deployment - Quick Start Guide

**Status:** ✅ Production Deployment Ready  
**Date:** 19. April 2026  
**Project:** Weather History Deutschland

---

## 📋 What Has Been Prepared

Ich habe alles vorbereitet für ein vollständiges Production-Deployment auf Hetzner:

### ✅ Deployment Files Created

| File | Purpose |
|------|---------|
| `DEPLOYMENT_HETZNER.md` | Complete 250+ line deployment guide |
| `docker/production/docker-compose.yml` | Updated for Hetzner multi-app setup |
| `scripts/deploy-production.sh` | One-command automated deployment |
| `scripts/verify-production.sh` | 9-point health check verification |
| `.env.production.example` | Environment configuration template |

### ✅ Configuration

```
Frontend Domain:  weather.elmarhepp.de
API Domain:       weather-api.elmarhepp.de
Frontend Port:    3030 (localhost only)
API Port:         3031 (localhost only)
Database:         PostgreSQL 15 (localhost:5432)
Cache:            Redis 7 (localhost:6379)
```

---

## 🎯 Next Steps - What YOU Need To Do

### Step 1️⃣: Prepare Your Hetzner Account

If you don't have a Hetzner server yet:

1. Go to https://www.hetzner.com/cloud
2. Create a Cloud project
3. Create a CX23 instance (2 vCPU, 4GB RAM) - **€4.90/month**
4. Choose:
   - **Image:** Ubuntu 24.04 (or 22.04)
   - **SSH Key:** Add your public key (~/.ssh/id_rsa.pub)
   - **Location:** Choose based on your location
5. Note down the **server IP address**

### Step 2️⃣: Configure Your Domain DNS

At your domain registrar (Spaceship or similar):

Set these DNS records pointing to your Hetzner IP:

```
Record Type    Host    Value
─────────────────────────────────
A              @       <your-hetzner-ip>
A              *       <your-hetzner-ip>
```

This makes `weather.elmarhepp.de` and `weather-api.elmarhepp.de` both point to your server.

### Step 3️⃣: Run the Deployment Script

From your local machine:

```bash
cd /Users/elmarhepp/workspace/weather-history

# Run the automated deployment
bash scripts/deploy-production.sh 192.0.2.1 your-email@example.com
```

Replace:
- `192.0.2.1` with your actual Hetzner IP
- `your-email@example.com` with an email for Let's Encrypt notifications

**What this script does:**
- ✅ Validates all local changes are committed
- ✅ Pushes to GitHub (origin/main)
- ✅ SSHs to Hetzner and clones the repository
- ✅ Creates secure environment files
- ✅ Starts all Docker containers
- ✅ Runs database migrations
- ✅ Configures Nginx on the host
- ✅ Sets up SSL certificates
- ✅ Verifies all systems

### Step 4️⃣: Verify the Deployment

After deployment completes, SSH into your server:

```bash
ssh root@<your-hetzner-ip>
cd /var/www/weather-history

# Run health check
bash scripts/verify-production.sh
```

This will check:
- Docker container status
- Port bindings (3030, 3031)
- Database connection
- Nginx configuration
- SSL certificates
- Disk space
- Application logs

### Step 5️⃣: Access Your Application

Once DNS propagates (5-60 minutes):

**Frontend:** https://weather.elmarhepp.de  
**API:** https://weather-api.elmarhepp.de/api/v1/stations

---

## 📊 What Gets Deployed

### Frontend (Vue 3)
- ✅ Interactive dashboard with 20 German weather stations
- ✅ Map view with Leaflet
- ✅ Advanced search and filtering
- ✅ Trends visualization
- ✅ Export/Import UI

### Backend API (Laravel 11)
- ✅ 20+ REST endpoints
- ✅ Climate normals (1991-2020)
- ✅ Trends analysis (temperature, precipitation, sunshine)
- ✅ Rankings and aggregates
- ✅ Export/Import functionality

### Database (PostgreSQL 15)
- ✅ 458,707 historical measurements (1890-2026)
- ✅ 20 German weather stations
- ✅ Yearly and monthly aggregates
- ✅ Climate normals reference data

### Infrastructure
- ✅ Nginx reverse proxy (SSL/TLS)
- ✅ Redis caching
- ✅ Automated Let's Encrypt certificates
- ✅ Health checks and monitoring

---

## 🔧 Maintenance After Deployment

### Check Status
```bash
# SSH into your server
ssh root@<your-hetzner-ip>
cd /var/www/weather-history

# View container status
docker compose -f docker/production/docker-compose.yml ps

# View logs
docker compose -f docker/production/docker-compose.yml logs -f laravel-backend
```

### Update Application
```bash
cd /var/www/weather-history
git pull origin main
docker compose -f docker/production/docker-compose.yml up -d --build
```

### Update Weather Data (Daily)
```bash
# Manual import for a station
docker compose -f docker/production/docker-compose.yml exec python-etl \
  python scripts/dwd_importer.py --station=01048

# Or set up a cronjob (configured in deploy script)
# Runs automatically at 3 AM UTC each day
```

### View Logs
```bash
# Backend API logs
docker compose -f docker/production/docker-compose.yml logs laravel-backend --tail 100

# Frontend logs  
docker compose -f docker/production/docker-compose.yml logs vue-frontend --tail 50

# Nginx logs (on host)
sudo journalctl -u nginx -n 50
```

---

## 🆘 Troubleshooting

### "Connection refused" when accessing website
- Check DNS propagation: `nslookup weather.elmarhepp.de`
- Check Nginx status: `sudo systemctl status nginx`
- Check port bindings: `netstat -tlnp | grep 3030`

### "Bad Gateway" (502)
- Check Laravel backend: `docker ps | grep laravel`
- Check API logs: `docker compose -f docker/production/docker-compose.yml logs laravel-backend`
- Restart: `docker compose -f docker/production/docker-compose.yml restart laravel-backend`

### "Connection to database failed"
- Check PostgreSQL: `docker ps | grep postgres`
- Test connection: `docker compose -f docker/production/docker-compose.yml exec postgres psql -U weather_user -d weather_history -c "SELECT 1"`

### Need to reset everything
```bash
# CAUTION: This deletes all data!
docker compose -f docker/production/docker-compose.yml down -v
docker compose -f docker/production/docker-compose.yml up -d --build
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan migrate --force
docker compose -f docker/production/docker-compose.yml exec laravel-backend php artisan weather:compute-aggregates
```

---

## 📈 Performance Expectations

### Expected Uptime
- ✅ 99.5%+ (depends on Hetzner infrastructure)
- ✅ Automatic restarts if containers fail
- ✅ Daily backups recommended

### Expected Response Times
- Frontend homepage: <200ms
- API /stations: <100ms
- API /climate-normals: <150ms
- API /trends: <200ms (depends on data size)

### Database Capacity
- Current: 458,707 measurements
- Growth rate: ~120 measurements/day
- Projected 5-year: 678,000+ measurements
- CX23 storage: 40GB SSD (plenty of room)

---

## 💡 Tips & Best Practices

### 1. Monitor Your Logs
```bash
# Watch logs in real-time
docker compose -f docker/production/docker-compose.yml logs -f
```

### 2. Regular Backups
```bash
# Manual database backup
docker compose -f docker/production/docker-compose.yml exec postgres \
  pg_dump -U weather_user weather_history | gzip > backup-$(date +%Y%m%d).sql.gz

# Add to crontab for automatic backups
0 4 * * * docker compose -f /var/www/weather-history/docker/production/docker-compose.yml exec -T postgres pg_dump -U weather_user weather_history | gzip > /var/backups/weather-$(date +\%Y\%m\%d).sql.gz
```

### 3. Update Let's Encrypt Certificates (automatic)
```bash
# Verify auto-renewal is working
sudo certbot renew --dry-run

# Manual renewal if needed
sudo certbot renew --force-renewal
```

### 4. Monitor Disk Space
```bash
# Check disk usage
df -h /var/www/weather-history

# Check database size
docker compose -f docker/production/docker-compose.yml exec postgres \
  psql -U weather_user -d weather_history -c "SELECT pg_size_pretty(pg_database_size('weather_history'));"
```

---

## 📞 Questions or Issues?

1. **Check the full documentation:** [DEPLOYMENT_HETZNER.md](./DEPLOYMENT_HETZNER.md)
2. **Review logs:** `docker compose logs -f`
3. **Run verification:** `bash scripts/verify-production.sh`
4. **Check API directly:** `curl https://weather-api.elmarhepp.de/api/v1/stations`

---

## ✅ Summary

**What's Ready:**
- ✅ All code is production-ready
- ✅ Docker infrastructure configured
- ✅ Deployment scripts automated
- ✅ SSL/TLS setup included
- ✅ Monitoring and health checks prepared

**Your Next Action:**
```bash
bash scripts/deploy-production.sh <your-hetzner-ip> <your-email@example.com>
```

**Timeline:**
- Deployment script execution: **~5-10 minutes**
- DNS propagation: **5-60 minutes**
- SSL certificate generation: **~30 seconds**
- Total time to live: **5-70 minutes**

---

**Status: ✅ READY FOR PRODUCTION DEPLOYMENT**

🎉 Your Weather History Deutschland application is ready to go live on Hetzner!
