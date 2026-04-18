# Projektplan: Komplexe Wetter-Website mit historischen DWD-Daten

## 📋 Überblick

Dieser Dokument beschreibt den Plan für die Entwicklung einer komplexen Wetter-Website, die historische Daten der deutschen Wetterstationen vom Deutschen Wetterdienst (DWD) verarbeitet und darstellt. Die Datenquelle ist der DWD Open Data Server unter https://opendata.dwd.de/.

## 📊 Die DWD-Datenstruktur

### Hierarchische Organisation
Der DWD bietet einen umfangreichen Schatz an historischen Wetterdaten in einer FTP-ähnlichen Verzeichnisstruktur:

```
opendata.dwd.de/
├── climate_environment/
│   ├── CDC/
│   │   ├── observations_germany/          # Messdaten deutscher Stationen
│   │   │   ├── climate/
│   │   │   │   ├── daily/                 # Tägliche Daten
│   │   │   │   │   ├── kl/                # Klimadaten (KL-Format)
│   │   │   │   │   │   ├── historical/    # Qualitätsgeprüfte Daten
│   │   │   │   │   │   └── recent/        # Aktuelle, ungeprüfte Daten
│   │   │   │   ├── hourly/                # Stündliche Daten
│   │   │   │   ├── monthly/               # Monatliche Daten
│   │   │   │   └── ...
│   │   │   └── ...
│   │   ├── derived_germany/               # Abgeleitete Parameter
│   │   ├── grids_germany/                 # Rasterdaten Deutschland
│   │   ├── regional_averages_DE/          # Bundesland-Mittelwerte
│   │   └── observations_global/           # Weltweite Daten
└── weather/                               # Wettervorhersagen
```

### Zeitliche Auflösungen
- **1-Minuten-Daten**: Niederschlag
- **10-Minuten-Daten**: Temperatur, Niederschlag, Wind, Sonnenscheindauer
- **Stündliche Daten**: Verschiedene Parameter
- **Tägliche Daten (KL-Format)**: Hauptklimaparameter
- **Monatliche/Jährliche Daten**: Aggregierte Werte
- **Vieljährige Mittel**: 30-jährige Klimanormalperioden

### Datenqualitätsstufen
1. **`historical/`**: Routinemäßige Qualitätskontrolle abgeschlossen
2. **`recent/`**: Aktuelle Daten, Qualitätskontrolle noch nicht vollständig
3. **`now/`**: Allerneueste 1-minütige und 10-minütige Daten

### Beispiel für tägliche Klimadaten
- **Pfad**: `/climate_environment/CDC/observations_germany/climate/daily/kl/historical/`
- **Dateiformat**: ZIP-Archive pro Station
- **Beispiel**: `tageswerte_KL_00001_19370101_19860630_hist.zip`
- **Inhalt**: Zeitreihendaten + Stationsmetadaten im CSV/TXT-Format

## 🚨 Herausforderungen

### Technische Herausforderungen
1. **Keine REST-API**: Nur Dateidownload verfügbar → Eigene Datenpipeline notwendig
2. **Datenvolumen**: Hunderte Stationen mit >100 Jahren Daten → Mehrere GB Rohdaten
3. **Dateiformate**: CSV/TXT mit festen Spaltenformaten, teils deutsche Umlaute
4. **Dateninhomogenitäten**: Stationsverlegungen, Gerätewechsel erfordern Metadatenanalyse
5. **Aktualisierungslogik**: Regelmäßige Updates erfordern differenzielles Download-Management

### Fachliche Herausforderungen
1. **Meteorologisches Wissen**: Verständnis der Parameter (TMK = Tagesmittel Temperatur, etc.)
2. **Datenqualität**: Fehlwerte, Plausibilitätsprüfungen, Qualitätsflags
3. **Visualisierung**: Sinnvolle Darstellung komplexer Zeitreihen und räumlicher Muster

## 🚀 MVP-Strategie für Hetzner CX23 Server

### Server-Spezifikationen
- **CPU**: 2 vCPUs (AMD EPYC)
- **RAM**: 4 GB
- **Storage**: 40 GB NVMe SSD
- **Aktuelle Last**: 4 kleine Apps mit geringer Auslastung

### Angepasste Scope für MVP
1. **Stationen**: 15-20 ausgewählte Hauptstationen (statt 400+)
2. **Zeitraum**: 1990-2024 (34 Jahre statt 100+)
3. **Parameter**: Kernparameter (Temperatur max/min/mittel, Niederschlag, Sonnenscheindauer)
4. **Auflösung**: Nur tägliche Daten (KL-Format)
5. **Datenvolumen**: ~2-3 Millionen Datensätze (statt ~14,6 Millionen)

### Technische Anpassungen für begrenzte Ressourcen
- **PostgreSQL-Optimierung**: `shared_buffers = 512MB`, `work_mem = 16MB`
- **Docker Resource Limits**: PostgreSQL (1.5 GB), Backend (512 MB), Redis (256 MB)
- **Aggressives Caching**: Redis für häufige Abfragen, Nginx Cache für statische Assets
- **Regelmäßige Wartung**: Automatisches VACUUM, Index-Optimierung, Cleanup temporärer Daten

### Hetzner Deployment Modell
- **Frontend Domain**: `wetter.elmarhepp.de`
- **API Domain**: `wetter-api.elmarhepp.de`
- **Deployment-Pfad**: `/var/www/wetter-app`
- **Ports**: WEB_PORT=3031, API_PORT=3032
- **HTTPS**: Let's Encrypt via Certbot

### Monitoring & Skalierungsplan
- **Phase 1**: MVP auf CX23 mit reduziertem Datensatz
- **Phase 2**: Performance-Monitoring (RAM, CPU, Disk, Query-Performance)
- **Phase 3**: Entscheidung basierend auf Monitoring:
  - **Option A**: Beibehalten mit optimiertem Scope
  - **Option B**: Upgrade auf CX41 (4 vCPU, 8 GB RAM, 80 GB)
  - **Option C**: Separate Cloud-Datenbank für historische Daten

## 🏗️ Architekturvorschlag (angepasst für MVP)

### Phase 1: Datenexploration & Prototyping
- **Ziel**: Verständnis der Datenstrukturen und -formate
- **Aktivitäten**:
  - Download und Analyse beispielhafter ZIP-Dateien
  - Entwicklung eines Prototyp-Parsers für DWD-Datenformate
  - Erkundung der Metadatenstruktur

### Phase 2: Datenbankdesign
- **Datenbank**: PostgreSQL mit Erweiterungen
  - **PostGIS**: Geografische Abfragen (Stationenkoordinaten)
  - **TimescaleDB**: Optimierte Zeitreihenverarbeitung
- **Tabellenstruktur**:
  ```sql
  -- Stationsmetadaten
  CREATE TABLE stations (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100),
    lat DECIMAL(9,6),
    lon DECIMAL(9,6),
    elevation INTEGER,
    state VARCHAR(50),
    start_date DATE,
    end_date DATE,
    active BOOLEAN
  );
  
  -- Tägliche Messungen
  CREATE TABLE daily_measurements (
    station_id VARCHAR(10) REFERENCES stations(id),
    date DATE,
    temp_max DECIMAL(5,2),    -- Maximumtemperatur
    temp_min DECIMAL(5,2),    -- Minimumtemperatur
    temp_mean DECIMAL(5,2),   -- Mitteltemperatur
    precipitation DECIMAL(5,1), -- Niederschlag
    sunshine DECIMAL(4,1),     -- Sonnenscheindauer
    snow_depth DECIMAL(4,1),   -- Schneehöhe
    quality_flags JSONB,       -- Qualitätsflags
    PRIMARY KEY (station_id, date)
  );
  
  -- Vorberechnete Aggregationen für Performance
  CREATE TABLE monthly_aggregates (
    station_id VARCHAR(10),
    year INTEGER,
    month INTEGER,
    temp_mean DECIMAL(5,2),
    precipitation_sum DECIMAL(7,1),
    sunshine_sum DECIMAL(6,1),
    PRIMARY KEY (station_id, year, month)
  );
  ```

### Phase 3: Datenpipeline (ETL)
- **Download-Skript**: Automatisierter Download neuer/aktualisierter ZIPs
- **Extraktion**: Entpacken der ZIP-Archive, Parsen der CSV/TXT-Dateien
- **Transformation**:
  - Datenbereinigung (Fehlwerte, Plausibilitätsprüfungen)
  - Einheitliche Formatierung
  - Qualitätsflag-Interpretation
- **Laden**: Import in die Datenbank
- **Scheduling**: Cron-Jobs oder Apache Airflow für regelmäßige Updates

### Phase 4: Backend-API (Laravel)
- **Framework**: Laravel 11 (PHP) mit Laravel Sanctum für API-Authentifizierung
- **Architektur**: Monolithisch mit API-Ressourcen und Service-Klassen
- **Datenbank-Integration**: Eloquent ORM mit PostgreSQL + TimescaleDB + PostGIS
- **API-Endpunkte**:
  ```
  GET    /api/stations                    # Liste aller Stationen
  GET    /api/stations/{id}               # Details einer Station
  GET    /api/stations/search?q={query}   # Stationssuche
  GET    /api/measurements                # Zeitreihendaten
  GET    /api/statistics                  # Klimakennwerte
  GET    /api/maps/stations               # Geojson für Karten
  POST   /api/exports                     # Datenexport
  ```
- **Filterparameter**:
  - Station(s)
  - Zeitraum (von/bis)
  - Parameter (Temperatur, Niederschlag, etc.)
  - Aggregation (täglich, monatlich, jährlich)
- **Laravel-Spezifika**:
  - API Resources für JSON-Formatierung
  - Form Requests für Validierung
  - Service-Klassen für Business-Logik
  - Jobs/Queues für Hintergrundverarbeitung
  - Caching mit Redis

### Phase 5: Frontend-Entwicklung (Vue.js)
- **Framework**: Vue.js 3 mit TypeScript + Composition API + Vite Build Tool
- **State Management**: Pinia (offizieller Vue.js Store)
- **Styling**: Tailwind CSS für responsive Design
- **UI-Komponenten**: PrimeVue oder Element Plus für vorgefertigte Komponenten
- **Hauptkomponenten**:
  1. **Kartenansicht**: Leaflet/Vue-Leaflet mit Stationspunkten
  2. **Diagramm-Dashboard**: Chart.js/vue-chartjs für Zeitreihenvisualisierung
  3. **Stationsauswahl**: Filter- und Suchkomponente mit Vue Router
  4. **Parameterauswahl**: Auswahl der anzuzeigenden Klimaparameter
  5. **Export-Tool**: Datenexport in verschiedenen Formaten
- **Vue.js-Spezifika**:
  - Single File Components (.vue)
  - Composition API für bessere Wiederverwendbarkeit
  - Vue Router für Navigation
  - VueUse für Utility-Composables

### Phase 6: Visualisierung
- **Karten**: Leaflet/Mapbox mit Heatmaps, Punkt-Layern
- **Diagramme**:
  - **Zeitreihen**: D3.js für komplexe, interaktive Diagramme
  - **Standarddiagramme**: Chart.js für Balken-, Linien-, Streudiagramme
  - **Klimadiagramme**: Walter-Lieth Diagramme, Klimadiagramme
- **Interaktivität**: Tooltips, Zoom, Vergleichsmöglichkeiten

### Phase 7: Erweiterte Features
- Stationsvergleiche (Side-by-side Visualisierung)
- Klimakennwert-Berechnung (Eistage, Sommertage, Tropennächte)
- Trendanalyse (lineare Regression, Moving Averages)
- Benutzerkonten für gespeicherte Analysen
- Alert-System für Extremwerte
- Mobile App (React Native)

## 🛠️ Technologie-Stack (angepasst für Laravel + Vue.js)

### Backend (Laravel)
| Komponente | Empfehlung | Alternative |
|------------|------------|-------------|
| Programmiersprache | PHP 8.2+ | |
| Web-Framework | Laravel 11 | Symfony, Lumen |
| ORM | Eloquent ORM | Doctrine |
| API-Authentifizierung | Laravel Sanctum | Laravel Passport |
| Task Queue | Laravel Queues + Redis | RabbitMQ, Beanstalkd |
| API-Dokumentation | Laravel API Documentation Generator | Scribe, OpenAPI |
| Caching | Redis + Laravel Cache | Memcached |
| Job Processing | Laravel Horizon (für Redis) | Supervisor |

### Datenbank
| Komponente | Empfehlung | Alternative |
|------------|------------|-------------|
| Hauptdatenbank | PostgreSQL 15+ | MySQL 8.0+, MariaDB |
| Zeitreihen-Erweiterung | TimescaleDB | Citus, native Partitioning |
| Geodaten | PostGIS | MySQL Spatial, MongoDB Geospatial |
| Cache | Redis (für Laravel Cache & Session) | Memcached |
| Search (optional) | Laravel Scout + Meilisearch | Elasticsearch, Algolia |

### Frontend (Vue.js)
| Komponente | Empfehlung | Alternative |
|------------|------------|-------------|
| Framework | Vue.js 3 + Composition API | Vue 2 (Options API) |
| Sprache | TypeScript | JavaScript |
| Build Tool | Vite | Vue CLI, Webpack |
| State Management | Pinia (offizieller Vue Store) | Vuex |
| UI Library | PrimeVue oder Element Plus | Vuetify, Quasar |
| Router | Vue Router | |
| HTTP Client | Axios + Laravel Sanctum | Fetch API, Vue-Query |
| Charts | Chart.js + vue-chartjs | ApexCharts, ECharts |
| Maps | Leaflet + Vue-Leaflet | Mapbox GL JS, OpenLayers |
| Utility Hooks | VueUse | |
| Form Handling | VeeValidate + FormKit | Vue Formulate, Vuelidate |

### ETL & Datenverarbeitung (Python)
| Komponente | Empfehlung | Alternative |
|------------|------------|-------------|
| Programmiersprache | Python 3.11+ | |
| DWD-Daten-Parsing | wetterdienst-Package | Eigenentwicklung |
| Datenbereinigung | Pandas | Polars, NumPy |
| Datenbank-Import | psycopg2 + SQLAlchemy | asyncpg, Django ORM |
| Scheduling | Cron + Laravel Scheduler | Celery, Airflow |

### Infrastruktur & DevOps
| Bereich | Empfehlung | Alternative |
|---------|------------|-------------|
| Containerisierung | Docker + Docker Compose | Podman |
| PHP-Container | php:8.2-fpm + Nginx | Apache, Caddy |
| Node/Vue-Container | Node:18-alpine + Nginx | |
| PostgreSQL-Container | timescale/timescaledb-postgis | postgres:15 |
| Orchestrierung | Docker Compose (für MVP) | Kubernetes (später) |
| CI/CD | GitHub Actions | GitLab CI, Jenkins |
| Monitoring | Laravel Telescope + Prometheus | New Relic, Datadog |
| Logging | Laravel Logging + ELK Stack | Papertrail, Loggly |

## 📈 Projekt-Roadmap für MVP (angepasst für Hetzner CX23)

### Phase 1: MVP-Entwicklung (Woche 1-4)
- [ ] **Datenexploration**: DWD-Datenstruktur analysieren, Beispiel-Daten downloaden
- [ ] **Parser-Entwicklung**: Python-Parser für KL-ZIP/CSV-Dateien erstellen
- [ ] **Datenbank-Setup**: PostgreSQL mit TimescaleDB & PostGIS in Docker
- [ ] **ETL-Pipeline**: Basis-ETL für 5 Test-Stationen entwickeln
- [ ] **Backend-Grundgerüst**: FastAPI mit Grund-API (Stations, Measurements)

### Phase 2: MVP-Ausbau (Woche 5-8)
- [ ] **Datenimport**: 15-20 ausgewählte Stationen (1990-2024) importieren
- [ ] **API-Vervollständigung**: Filter, Aggregationen, Statistiken
- [ ] **Frontend-Grundgerüst**: React mit TypeScript, Tailwind CSS
- [ ] **Kartenintegration**: Leaflet mit Stationspunkten
- [ ] **Basis-Diagramme**: Chart.js für Zeitreihenvisualisierung

### Phase 3: MVP-Optimierung (Woche 9-10)
- [ ] **Performance-Optimierung**: Datenbank-Indizes, Caching (Redis)
- [ ] **Responsive Design**: Mobile/Desktop Optimierung
- [ ] **Datenexport**: CSV/JSON Export-Funktionalität
- [ ] **Testing**: Unit Tests für kritische Komponenten

### Phase 4: MVP-Deployment (Woche 11-12)
- [ ] **Docker-Compose-Produktion**: Resource Limits für CX23
- [ ] **Hetzner-Deployment**: Nginx-Konfiguration, Certbot-Setup
- [ ] **Monitoring**: Basis-Monitoring (RAM, CPU, Disk)
- [ ] **Dokumentation**: Setup- und Bedienungsanleitung

### Phase 5: Evaluation & Skalierung (ab Woche 13)
- [ ] **Performance-Monitoring**: Auslastung auf CX23 analysieren
- [ ] **User-Feedback**: Erste Nutzung und Feedback sammeln
- [ ] **Skalierungsentscheidung**: 
  - Option A: Beibehalten mit optimiertem Scope
  - Option B: Upgrade auf CX41 (4 vCPU, 8 GB RAM)
  - Option C: Cloud-Datenbank für historische Daten
- [ ] **Erweiterungen**: Basierend auf Performance und Feedback

## 💡 Erfolgsfaktoren & Best Practices

### Starten Sie klein
- Beginnen mit 10-20 wichtigen Stationen statt allen 400+
- Fokussieren auf Kernparameter (Temperatur, Niederschlag, Sonne)
- Iterative Entwicklung mit frühem Feedback

### Wiederverwendung von Code
- **Python-Pakete**: `wetterdienst`, `dwd` für DWD-Datenzugriff
- **Frontend-Komponenten**: Bestehende Chart-Bibliotheken nutzen
- **Boilerplate**: Templates für FastAPI/React beschleunigen den Start

### Cloud-Optionen
- **Database-as-a-Service**: Supabase, AWS RDS, Google Cloud SQL
- **Hosting**: Vercel (Frontend), Railway/Render (Backend), AWS ECS
- **Storage**: S3 für Rohdaten-Archive

### Qualitätssicherung
- Unit Tests für Datenparsing und Business-Logik
- Integration Tests für API-Endpunkte
- E2E Tests für kritische User Journeys
- Code Reviews und Pair Programming

### Dokumentation
- API-Dokumentation mit OpenAPI/Swagger
- Datenbank-Dokumentation (Schema, Beziehungen)
- Benutzerhandbuch für die Website
- Entwickler-Dokumentation (Setup, Deployment)

## 🎯 Konkrete nächste Schritte

### 1. Setup-Entwicklungsumgebung
- [ ] **Git-Repository einrichten**: Mit strukturierten Ordnern (laravel-backend/, vue-frontend/, etl-python/, docker/)
- [ ] **Docker-Compose für Entwicklung**: Laravel (PHP-FPM + Nginx), Vue.js (Node), PostgreSQL mit TimescaleDB + PostGIS, Redis
- [ ] **Python-Umgebung für ETL**: wetterdienst-Package, Pandas, SQLAlchemy für Datenimport
- [ ] **Laravel Setup**: Laravel 11 Installation mit PostgreSQL-Treiber und notwendigen Paketen
- [ ] **Vue.js Setup**: Vue 3 mit TypeScript, Vite, Pinia, Vue Router, Tailwind CSS

### 2. Datenexploration & Parser-Entwicklung
- [ ] **DWD-Daten analysieren**: 2-3 Beispiel-ZIPs von historischen KL-Daten downloaden
- [ ] **Metadaten-Struktur**: Stationsinformationen und Dateiformate verstehen
- [ ] **Python-Parser entwickeln**: Für ZIP-Extraktion und CSV-Parsing der DWD-Daten
- [ ] **Test-Datenimport**: 5 Beispiel-Stationen in lokale Datenbank importieren (über Python-Skript)

### 3. MVP-Basisarchitektur
- [ ] **Laravel-Backend-Grundgerüst**: API-Routen, Controller, Eloquent Models für Stations und Measurements
- [ ] **Datenbank-Schema**: Migrationen für PostgreSQL mit TimescaleDB + PostGIS
- [ ] **Vue.js-Frontend-Grundgerüst**: Komponenten-Struktur, Router, Pinia Store für State Management
- [ ] **API-Integration**: Axios-Client für Kommunikation mit Laravel Backend
- [ ] **Docker-Config für Produktion**: Mit Resource-Limits für CX23 Server

### 4. Hetzner-Deployment-Vorbereitung
- [ ] **DNS-Einträge vorbereiten**:
  - `A`-Record für `wetter.elmarhepp.de` → Hetzner-Server-IP
  - `A`-Record für `wetter-api.elmarhepp.de` → Hetzner-Server-IP
- [ ] **Deployment-Skripte**: Docker-Compose-Production mit Ports 3031/3032
- [ ] **Nginx-Konfiguration**: Gemäß hetzner-multi-app-template.md für Laravel + Vue.js
- [ ] **Monitoring-Setup**: Basis-Monitoring für RAM, CPU, Disk, Laravel Telescope

## 🚀 Empfehlung für den Start (Laravel + Vue.js)

### MVP-Ansatz (empfohlen)
1. **Beginnen mit reduziertem Scope**: 15 Stationen, 1990-2024, nur Tageswerte
2. **Lokale Entwicklung**: Vollständige Funktionalität mit Docker Compose testen
3. **Staging auf Hetzner**: Mit limitiertem Datensatz deployen
4. **Performance-Monitoring**: Auslastung auf CX23 überwachen (Laravel Telescope + Server-Monitoring)
5. **Skalierung basierend auf Ergebnissen**: Bei Bedarf Upgrade auf CX41 oder separate Datenbank

### Technologie-Entscheidungen (angepasst für Laravel + Vue.js)
- **Backend**: Laravel 11 (PHP) + Eloquent ORM + Redis für Caching/Queues
- **Datenbank**: PostgreSQL 15 + TimescaleDB (für Zeitreihen) + PostGIS (für Geodaten)
- **Frontend**: Vue.js 3 + TypeScript + Pinia + Tailwind CSS
- **Visualisierung**: Chart.js + vue-chartjs für Basis-Diagramme, D3.js für komplexe Visualisierungen
- **Maps**: Leaflet + Vue-Leaflet für interaktive Karten
- **ETL/Data Processing**: Python mit wetterdienst-Package für DWD-Daten-Parsing
- **Deployment**: Docker Compose + Hetzner-Multi-App-Template + Nginx Reverse Proxy

### Vorteile von Laravel + Vue.js für dieses Projekt
1. **Laravel Eloquent ORM**: Elegante Datenbank-Abfragen für komplexe Zeitreihen
2. **Laravel Queues**: Hintergrundverarbeitung für Datenimporte und -aktualisierungen
3. **Laravel Sanctum**: Einfache API-Authentifizierung für zukünftige Benutzerkonten
4. **Vue.js Reactivity**: Echtzeit-Updates für Diagramme und Karten
5. **Vue Single File Components**: Saubere Trennung von Logik, Template und Styles
6. **PHP/Python Kombination**: Beste Werkzeuge für Web-API (PHP) und Datenverarbeitung (Python)

### Zeitlicher Rahmen (angepasst)
- **Woche 1-2**: Setup + Datenexploration + Python-Parser
- **Woche 3-4**: Laravel Backend + Datenbank-Migrationen + Basis-API
- **Woche 5-6**: Vue.js Frontend + Kartenintegration + Basis-Diagramme
- **Woche 7-8**: Datenimport (15 Stationen) + API-Filter + Caching
- **Woche 9-10**: Performance-Optimierung + Responsive Design + Testing
- **Woche 11-12**: Hetzner-Deployment + Monitoring + Dokumentation

## 📞 Umsetzungsunterstützung

Wenn Sie bereit sind mit der Umsetzung zu beginnen, können Sie mich in den **ACT MODE** schalten. Ich kann dann:

1. **Git-Repository strukturieren** mit Laravel + Vue.js + Python ETL Struktur
2. **Docker-Compose-Dateien** für Entwicklung und Produktion erstellen
3. **Laravel-Backend-Grundgerüst** mit API, Models, Migrationen, Controllern
4. **Vue.js-Frontend-Basis** mit TypeScript, Pinia, Vue Router, Tailwind CSS
5. **Python-ETL-Pipeline** für DWD-Daten-Parsing und -Import
6. **Hetzner-Deployment-Skripte** gemäß Ihrer Vorlage für Laravel + Vue.js

Sind Sie bereit mit der Umsetzung zu beginnen?

---

*Letzte Aktualisierung: 18. April 2026*  
*Autor: Cline (AI-Assistent)*  
*Projekt: Historische Wetterdaten Deutschland*