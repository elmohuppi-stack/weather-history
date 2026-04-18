"""
Konfiguration für DWD Daten-Importer
"""

import os
from pathlib import Path
from dotenv import load_dotenv

# Lade .env Datei
env_path = Path(__file__).parent.parent.parent / '.env'
load_dotenv(env_path)

# DWD Open Data Server URL
DWD_BASE_URL = "https://opendata.dwd.de"

# Datenbank-Konfiguration
DATABASE_URL = os.getenv('DATABASE_URL', 
    f"postgresql://{os.getenv('DB_USERNAME', 'weather_user')}:"
    f"{os.getenv('DB_PASSWORD', 'weather_password')}@"
    f"{os.getenv('DB_HOST', 'localhost')}:"
    f"{os.getenv('DB_PORT', '5432')}/"
    f"{os.getenv('DB_DATABASE', 'weather_history')}"
)

# Import-Einstellungen
DEFAULT_START_YEAR = 1990
DEFAULT_END_YEAR = 2024
BATCH_SIZE = 1000  # Anzahl Datensätze pro Batch-Insert

# Logging-Konfiguration
LOG_LEVEL = os.getenv('LOG_LEVEL', 'INFO')
LOG_FILE = os.getenv('LOG_FILE', 'logs/dwd_importer.log')

# Proxy-Einstellungen (falls benötigt)
HTTP_PROXY = os.getenv('HTTP_PROXY')
HTTPS_PROXY = os.getenv('HTTPS_PROXY')

# Timeout-Einstellungen
DOWNLOAD_TIMEOUT = int(os.getenv('DOWNLOAD_TIMEOUT', '300'))  # 5 Minuten
CONNECT_TIMEOUT = int(os.getenv('CONNECT_TIMEOUT', '30'))  # 30 Sekunden

# Verzeichnisse
DATA_DIR = Path(os.getenv('DATA_DIR', 'data'))
LOG_DIR = Path(os.getenv('LOG_DIR', 'logs'))
CACHE_DIR = Path(os.getenv('CACHE_DIR', 'cache'))

# Erstelle Verzeichnisse falls nicht vorhanden
for directory in [DATA_DIR, LOG_DIR, CACHE_DIR]:
    directory.mkdir(parents=True, exist_ok=True)

# Ausgewählte Stationen für MVP
SELECTED_STATIONS = [
    '01048',  # Berlin-Tempelhof
    '01358',  # Hamburg-Fuhlsbüttel
    '01050',  # München-Stadt
    '01270',  # Köln-Bonn
    '01420',  # Frankfurt/Main
    '01001',  # Bremen
    '01072',  # Dresden-Klotzsche
    '01078',  # Düsseldorf
    '01091',  # Essen
    '01103',  # Hannover
    '01161',  # Leipzig
    '01207',  # Nürnberg
    '01297',  # Stuttgart-Echterdingen
    '01303',  # Saarbrücken-Ensheim
    '01346',  # Rostock-Warnemünde
    '01427',  # Karlsruhe-Rheinstetten
]

# Parameter-Mapping für DWD-Daten
PARAMETER_MAPPING = {
    'TMK': 'temp_mean',      # Tagesmittel der Lufttemperatur
    'TXK': 'temp_max',       # Tagesmaximum der Lufttemperatur
    'TNK': 'temp_min',       # Tagesminimum der Lufttemperatur
    'RSK': 'precipitation',  # Tagesniederschlagssumme
    'SDK': 'sunshine',       # Tägliche Sonnenscheindauer
    'SHK_TAG': 'snow_depth', # Schneehöhe
}

# Qualitätsflags
QUALITY_FLAGS = {
    '0': 'keine Qualitätskontrolle',
    '1': 'plausibel',
    '2': 'zweifelhaft',
    '3': 'falsch',
    '4': 'nicht verfügbar',
    '5': 'interpoliert',
    '6': 'korrigiert',
    '7': 'geschätzt',
    '8': 'berechnet',
    '9': 'unbekannt',
}