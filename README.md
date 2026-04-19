# 🌤️ Weather History DWD

Eine Website für historische Wetter- und Klimastatistiken deutscher DWD-Stationen.

## 📋 Überblick

Dieses Projekt verarbeitet und visualisiert historische Wetterdaten von deutschen Wetterstationen, die vom DWD Open Data Server unter https://opendata.dwd.de/ bereitgestellt werden.

### Aktueller Produktfokus

- **rund 20 kuratierte Stationen** statt Vollabdeckung
- **möglichst tiefe historische Datenreihen** statt nur jüngerer Jahre
- **tägliche DWD-Beobachtungsdaten** als Rohbasis
- **Monats- und Jahresstatistiken** als fachlicher Kern
- **Kernparameter**: Temperatur, Niederschlag, Sonnenscheindauer, optional Schnee

## 🏗️ Architektur

### Technologie-Stack

- **Backend**: Laravel 11 (PHP) mit PostgreSQL + PostGIS
- **Frontend**: Vue.js 3 + TypeScript + Tailwind CSS
- **ETL/Data Processing**: Python mit wetterdienst-Package
- **Datenbank**: PostgreSQL 15 + TimescaleDB + PostGIS
- **Caching**: Redis
- **Containerisierung**: Docker + Docker Compose
- **Deployment**: Hetzner Cloud (CX23 Server)

### Projektstruktur

```
weather-history/
├── laravel-backend/          # Laravel 11 API
├── vue-frontend/            # Vue.js 3 Frontend
├── etl-python/             # Python ETL für DWD-Daten
├── docker/                 # Docker Konfigurationen
│   ├── development/        # Entwicklungsumgebung
│   └── production/         # Produktionsumgebung (Hetzner)
├── docs/                   # Dokumentation
└── scripts/               # Deployment-Skripte
```

## 🚀 Schnellstart

### Voraussetzungen

- Docker Desktop
- Git
- Node.js 18+ (für lokale Frontend-Entwicklung)
- Python 3.11+ (für ETL-Skripte)

### Entwicklungsumgebung starten

#### Empfohlener Schnellstart mit Make

```bash
make start
make etl
make health
```

Für einen gezielten Import einer Station:

```bash
make etl-station station=01048
```

1. **Repository klonen**

   ```bash
   git clone <repository-url>
   cd weather-history
   ```

2. **Docker-Container starten**

   ```bash
   cd docker/development
   docker-compose up -d
   ```

3. **Services zugreifen**
   - **Frontend**: http://localhost:3000
   - **Backend API**: http://localhost:8000
   - **Adminer (DB GUI)**: http://localhost:8080
   - **PostgreSQL**: localhost:5432
   - **Redis**: localhost:6379

4. **Laravel Backend einrichten**

   ```bash
   cd laravel-backend
   composer install
   php artisan migrate
   php artisan serve
   ```

5. **Vue.js Frontend einrichten**

   ```bash
   cd vue-frontend
   npm install
   npm run dev
   ```

6. **Python ETL ausführen**
   ```bash
   cd etl-python
   pip install -r requirements.txt
   python scripts/dwd_importer.py --init-db --import-all
   ```

## 📊 Datenstruktur

### DWD Datenquelle

- **URL**: https://opendata.dwd.de/
- **Format**: ZIP-Archive mit CSV/TXT-Dateien
- **Auflösung**: Tägliche Klimadaten (KL-Format)
- **Parameter**: Temperatur, Niederschlag, Sonnenscheindauer, Schneehöhe

### Datenbank-Schema

```sql
-- Stationsmetadaten
CREATE TABLE stations (
    id VARCHAR(10) PRIMARY KEY,      -- DWD Stations-ID
    name VARCHAR(100) NOT NULL,      -- Stationsname
    lat DECIMAL(9,6) NOT NULL,       -- Breitengrad
    lon DECIMAL(9,6) NOT NULL,       -- Längengrad
    elevation INTEGER,               -- Höhe über NN
    state VARCHAR(50),               -- Bundesland
    start_date DATE,                 -- Erster Datensatz
    end_date DATE,                   -- Letzter Datensatz
    active BOOLEAN DEFAULT TRUE      -- Station aktiv
);

-- Tägliche Messungen
CREATE TABLE daily_measurements (
    station_id VARCHAR(10) REFERENCES stations(id),
    date DATE,
    temp_max DECIMAL(5,2),           -- Maximumtemperatur
    temp_min DECIMAL(5,2),           -- Minimumtemperatur
    temp_mean DECIMAL(5,2),          -- Mitteltemperatur
    precipitation DECIMAL(5,1),      -- Niederschlag
    sunshine DECIMAL(4,1),           -- Sonnenscheindauer
    snow_depth DECIMAL(4,1),         -- Schneehöhe
    quality_flags JSONB,             -- Qualitätsflags
    PRIMARY KEY (station_id, date)
);
```

## 🎯 Features

### MVP Features

- [x] **Stationsübersicht** mit Kartenansicht
- [x] **Zeitreihen-Diagramme** für Temperatur, Niederschlag, Sonne
- [x] **Filterfunktionen** nach Station, Zeitraum, Parameter
- [x] **Responsive Design** für Desktop und Mobile
- [x] **Datenexport** als CSV/JSON
- [x] **Automatisierter Datenimport** von DWD

### Nächste fachliche Ausbaustufen

- [ ] Stationsset auf rund 20 Langzeitstationen erweitern
- [ ] Monats- und Jahresstatistiken vollständig integrieren
- [ ] Klimakennwerte wie Frosttage, Sommertage und Regentage berechnen
- [ ] Stationsvergleiche und Rankings aufbauen
- [ ] Kartenansicht von Placeholder auf echte Stationskarte umstellen
- [ ] Export- und Importansicht mit realen Backend-Prozessen verbinden

## 🔧 Entwicklung

### Backend (Laravel)

```bash
cd laravel-backend
composer install          # Abhängigkeiten installieren
php artisan migrate       # Datenbank-Migrationen
php artisan serve         # Entwicklungsserver starten
php artisan test          # Tests ausführen
```

### Frontend (Vue.js)

```bash
cd vue-frontend
npm install               # Abhängigkeiten installieren
npm run dev              # Entwicklungsserver starten
npm run build            # Produktions-Build
npm run test             # Tests ausführen
```

### Python ETL

```bash
cd etl-python
pip install -r requirements.txt  # Abhängigkeiten installieren

# Datenbank initialisieren
python scripts/dwd_importer.py --init-db

# Alle Stationen importieren
python scripts/dwd_importer.py --import-all

# Spezifische Station importieren
python scripts/dwd_importer.py --station 01048
```

## 🐳 Docker Entwicklung

### Container-Management

```bash
# Alle Services starten
docker-compose up -d

# Services stoppen
docker-compose down

# Logs anzeigen
docker-compose logs -f

# Container-Status
docker-compose ps

# In Container einsteigen
docker-compose exec laravel-backend bash
docker-compose exec postgres psql -U weather_user -d weather_history
```

### Datenbank-Operationen

```bash
# Datenbank-Backup
docker-compose exec postgres pg_dump -U weather_user weather_history > backup.sql

# Datenbank-Wiederherstellung
docker-compose exec -T postgres psql -U weather_user -d weather_history < backup.sql

# Datenbank löschen und neu erstellen
docker-compose down -v
docker-compose up -d
```

## 🚀 Deployment (Hetzner)

### Server-Voraussetzungen

- **Hetzner CX23 Server** (2 vCPU, 4 GB RAM, 40 GB SSD)
- **Ubuntu 24.04 LTS**
- **Docker + Docker Compose**

### Deployment-Skript

```bash
# Auf Hetzner Server ausführen
./scripts/deploy-hetzner.sh
```

### Domains

- **Frontend**: `wetter-dwd.elmarhepp.de`
- **API**: `wetter-dwd-api.elmarhepp.de`

## 📈 Monitoring & Wartung

### Server-Monitoring

- **RAM/CPU/Disk Usage**: Prometheus + Grafana
- **Application Logs**: Laravel Telescope
- **Database Performance**: pg_stat_statements
- **Error Tracking**: Sentry (optional)

### Wartungsaufgaben

- **Täglicher Datenimport**: Cronjob für DWD-Updates
- **Datenbank-Optimierung**: Wöchentliches VACUUM
- **Backup**: Tägliche Datenbank-Backups
- **Security Updates**: Monatliche System-Updates

## 🤝 Mitwirken

### Git Workflow

1. **Issue erstellen** für neue Features/Bugs
2. **Branch erstellen** von `main`
3. **Änderungen implementieren** mit Tests
4. **Pull Request erstellen** für Review
5. **Code Review** durchführen
6. **Merge in main** nach Approval

### Coding Standards

- **PHP**: PSR-12 mit Laravel Pint
- **JavaScript/TypeScript**: ESLint + Prettier
- **Python**: Black + isort
- **Git**: Conventional Commits

## 📄 Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert - siehe [LICENSE](LICENSE) Datei für Details.

## 🙏 Danksagung

- **Deutscher Wetterdienst (DWD)** für die Bereitstellung der Open Data
- **Laravel Community** für das exzellente PHP-Framework
- **Vue.js Community** für das moderne Frontend-Framework
- **Hetzner** für die zuverlässige Cloud-Infrastruktur

## 📞 Support

Bei Fragen oder Problemen:

1. **Issues** auf GitHub öffnen
2. **Dokumentation** konsultieren
3. **Community**-Foren nutzen

---

**Letzte Aktualisierung**: April 2026  
**Projektstatus**: MVP Entwicklung  
**Nächste Meilensteine**: Datenimport, API-Integration, Frontend-Visualisierung
