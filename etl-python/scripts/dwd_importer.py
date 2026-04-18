#!/usr/bin/env python3
"""
DWD Daten-Importer für historische Wetterdaten
Lädt tägliche Klimadaten (KL-Format) vom DWD Open Data Server
"""

import os
import sys
import logging
from pathlib import Path
from datetime import datetime, timedelta
from typing import List, Dict, Optional
import zipfile
import io

import pandas as pd
import numpy as np
import requests
from sqlalchemy import create_engine, text
from sqlalchemy.orm import sessionmaker
from tqdm import tqdm
from loguru import logger
import click

# Add parent directory to path for imports
sys.path.append(str(Path(__file__).parent.parent))

from models.database import Base, Station, DailyMeasurement
from config.settings import DATABASE_URL, DWD_BASE_URL

# Configure logging
logger.remove()
logger.add(sys.stderr, format="<green>{time:YYYY-MM-DD HH:mm:ss}</green> | <level>{level:8}</level> | <cyan>{name}</cyan>:<cyan>{function}</cyan>:<cyan>{line}</cyan> - <level>{message}</level>")

class DWDImporter:
    """Hauptklasse für den DWD-Datenimport"""
    
    def __init__(self, db_url: str = DATABASE_URL):
        self.db_url = db_url
        self.engine = create_engine(db_url)
        self.Session = sessionmaker(bind=self.engine)
        
        # DWD URLs für tägliche Klimadaten
        self.daily_kl_url = f"{DWD_BASE_URL}/climate_environment/CDC/observations_germany/climate/daily/kl/historical/"
        self.recent_kl_url = f"{DWD_BASE_URL}/climate_environment/CDC/observations_germany/climate/daily/kl/recent/"
        
        # Ausgewählte Stationen für MVP (15 wichtige Stationen)
        self.selected_stations = [
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
        ]
    
    def init_database(self):
        """Datenbank-Tabellen erstellen"""
        logger.info("Erstelle Datenbank-Tabellen...")
        Base.metadata.create_all(self.engine)
        logger.success("Datenbank-Tabellen erstellt")
    
    def download_station_data(self, station_id: str) -> Optional[pd.DataFrame]:
        """
        Lädt Daten für eine spezifische Station vom DWD Server
        
        Args:
            station_id: DWD Stations-ID (5-stellig)
            
        Returns:
            DataFrame mit den Stationsdaten oder None bei Fehler
        """
        try:
            # Versuche zuerst historische Daten
            filename = f"tageswerte_KL_{station_id}_*_hist.zip"
            url = f"{self.daily_kl_url}{filename}"
            
            logger.info(f"Lade Daten für Station {station_id}...")
            
            # Hier würde der eigentliche Download-Logik stehen
            # Für das MVP simulieren wir erstmal mit Dummy-Daten
            
            # Simulierte Daten für Entwicklung
            data = self._create_sample_data(station_id)
            return data
            
        except Exception as e:
            logger.error(f"Fehler beim Download von Station {station_id}: {e}")
            return None
    
    def _create_sample_data(self, station_id: str) -> pd.DataFrame:
        """Erstellt Beispieldaten für Entwicklung"""
        # Stationsinformationen (simuliert)
        stations_info = {
            '01048': {'name': 'Berlin-Tempelhof', 'lat': 52.47, 'lon': 13.40, 'elevation': 48},
            '01358': {'name': 'Hamburg-Fuhlsbüttel', 'lat': 53.63, 'lon': 10.00, 'elevation': 16},
            '01050': {'name': 'München-Stadt', 'lat': 48.14, 'lon': 11.57, 'elevation': 515},
            '01270': {'name': 'Köln-Bonn', 'lat': 50.87, 'lon': 7.17, 'elevation': 91},
            '01420': {'name': 'Frankfurt/Main', 'lat': 50.05, 'lon': 8.60, 'elevation': 112},
        }
        
        station_info = stations_info.get(station_id, {
            'name': f'Station {station_id}',
            'lat': 51.16,
            'lon': 10.45,
            'elevation': 200
        })
        
        # Erzeuge Beispieldaten für 2020-2024
        dates = pd.date_range(start='2020-01-01', end='2024-12-31', freq='D')
        n_days = len(dates)
        
        # Simulierte Wetterdaten
        np.random.seed(int(station_id))
        
        data = pd.DataFrame({
            'station_id': station_id,
            'date': dates,
            'temp_max': np.random.normal(15, 8, n_days).round(1),
            'temp_min': np.random.normal(5, 7, n_days).round(1),
            'temp_mean': np.random.normal(10, 6, n_days).round(1),
            'precipitation': np.random.exponential(2, n_days).round(1),
            'sunshine': np.random.uniform(0, 12, n_days).round(1),
            'snow_depth': np.where(np.random.random(n_days) > 0.95, 
                                  np.random.uniform(1, 20, n_days).round(1), 0),
            'quality_flags': '{}'
        })
        
        # Füge Stationsmetadaten hinzu
        data['station_name'] = station_info['name']
        data['lat'] = station_info['lat']
        data['lon'] = station_info['lon']
        data['elevation'] = station_info['elevation']
        
        return data
    
    def import_station(self, station_id: str) -> bool:
        """
        Importiert Daten für eine einzelne Station
        
        Args:
            station_id: DWD Stations-ID
            
        Returns:
            True bei Erfolg, False bei Fehler
        """
        try:
            session = self.Session()
            
            # Prüfe ob Station bereits existiert
            existing_station = session.query(Station).filter_by(id=station_id).first()
            
            if not existing_station:
                # Lade Stationsdaten
                data = self.download_station_data(station_id)
                
                if data is None or data.empty:
                    logger.warning(f"Keine Daten für Station {station_id} gefunden")
                    return False
                
                # Extrahiere Stationsinformationen
                station_info = data.iloc[0]
                
                # Erstelle Station
                station = Station(
                    id=station_id,
                    name=station_info.get('station_name', f'Station {station_id}'),
                    lat=float(station_info.get('lat', 0)),
                    lon=float(station_info.get('lon', 0)),
                    elevation=int(station_info.get('elevation', 0)),
                    state=self._get_state_from_coords(
                        float(station_info.get('lat', 0)),
                        float(station_info.get('lon', 0))
                    ),
                    start_date=data['date'].min().date(),
                    end_date=data['date'].max().date(),
                    active=True
                )
                
                session.add(station)
                session.commit()
                logger.info(f"Station {station_id} ({station.name}) hinzugefügt")
            
            # Importiere Messdaten
            self._import_measurements(station_id, session)
            
            session.close()
            return True
            
        except Exception as e:
            logger.error(f"Fehler beim Import von Station {station_id}: {e}")
            return False
    
    def _import_measurements(self, station_id: str, session):
        """Importiert Messdaten für eine Station"""
        data = self.download_station_data(station_id)
        
        if data is None or data.empty:
            return
        
        logger.info(f"Importiere {len(data)} Messungen für Station {station_id}...")
        
        # Konvertiere zu DailyMeasurement Objekten
        measurements = []
        for _, row in tqdm(data.iterrows(), total=len(data), desc=f"Station {station_id}"):
            measurement = DailyMeasurement(
                station_id=station_id,
                date=row['date'].date(),
                temp_max=float(row['temp_max']) if pd.notna(row['temp_max']) else None,
                temp_min=float(row['temp_min']) if pd.notna(row['temp_min']) else None,
                temp_mean=float(row['temp_mean']) if pd.notna(row['temp_mean']) else None,
                precipitation=float(row['precipitation']) if pd.notna(row['precipitation']) else None,
                sunshine=float(row['sunshine']) if pd.notna(row['sunshine']) else None,
                snow_depth=float(row['snow_depth']) if pd.notna(row['snow_depth']) else None,
                quality_flags=row['quality_flags']
            )
            measurements.append(measurement)
        
        # Batch-Insert
        session.bulk_save_objects(measurements)
        session.commit()
        
        logger.success(f"{len(measurements)} Messungen für Station {station_id} importiert")
    
    def _get_state_from_coords(self, lat: float, lon: float) -> str:
        """Ermittelt Bundesland aus Koordinaten (vereinfacht)"""
        # Vereinfachte Zuordnung für MVP
        if 53.0 <= lat <= 54.5 and 10.0 <= lon <= 12.0:
            return 'Schleswig-Holstein'
        elif 53.0 <= lat <= 54.0 and 12.0 <= lon <= 14.0:
            return 'Mecklenburg-Vorpommern'
        elif 52.0 <= lat <= 53.5 and 10.0 <= lon <= 12.0:
            return 'Niedersachsen'
        elif 52.0 <= lat <= 53.0 and 12.0 <= lon <= 14.0:
            return 'Brandenburg'
        elif 51.0 <= lat <= 52.5 and 10.0 <= lon <= 12.0:
            return 'Nordrhein-Westfalen'
        elif 51.0 <= lat <= 52.0 and 12.0 <= lon <= 14.0:
            return 'Sachsen-Anhalt'
        elif 50.0 <= lat <= 51.5 and 10.0 <= lon <= 12.0:
            return 'Hessen'
        elif 50.0 <= lat <= 51.0 and 12.0 <= lon <= 14.0:
            return 'Thüringen'
        elif 49.0 <= lat <= 50.5 and 10.0 <= lon <= 12.0:
            return 'Bayern'
        elif 49.0 <= lat <= 50.0 and 12.0 <= lon <= 14.0:
            return 'Sachsen'
        elif 48.0 <= lat <= 49.5 and 10.0 <= lon <= 12.0:
            return 'Baden-Württemberg'
        elif 49.0 <= lat <= 50.0 and 6.0 <= lon <= 8.0:
            return 'Rheinland-Pfalz'
        elif 49.0 <= lat <= 50.0 and 8.0 <= lon <= 10.0:
            return 'Saarland'
        elif 52.4 <= lat <= 52.6 and 13.2 <= lon <= 13.6:
            return 'Berlin'
        elif 53.5 <= lat <= 53.7 and 9.9 <= lon <= 10.1:
            return 'Hamburg'
        else:
            return 'Deutschland'
    
    def import_all_selected(self):
        """Importiert alle ausgewählten Stationen"""
        logger.info(f"Importiere {len(self.selected_stations)} ausgewählte Stationen...")
        
        success_count = 0
        for station_id in tqdm(self.selected_stations, desc="Stationen importieren"):
            if self.import_station(station_id):
                success_count += 1
        
        logger.success(f"{success_count}/{len(self.selected_stations)} Stationen erfolgreich importiert")
        return success_count
    
    def update_recent_data(self):
        """Aktualisiert Daten mit den neuesten verfügbaren"""
        logger.info("Aktualisiere neueste Daten...")
        # Hier würde die Logik für Updates stehen
        logger.info("Update-Funktionalität für MVP noch nicht implementiert")

@click.command()
@click.option('--init-db', is_flag=True, help='Datenbank initialisieren')
@click.option('--import-all', is_flag=True, help='Alle ausgewählten Stationen importieren')
@click.option('--station', help='Spezifische Station importieren (z.B. 01048)')
@click.option('--update', is_flag=True, help='Neueste Daten aktualisieren')
def main(init_db: bool, import_all: bool, station: str, update: bool):
    """DWD Daten-Importer CLI"""
    
    importer = DWDImporter()
    
    if init_db:
        importer.init_database()
    
    if station:
        importer.import_station(station)
    
    if import_all:
        importer.import_all_selected()
    
    if update:
        importer.update_recent_data()
    
    if not any([init_db, import_all, station, update]):
        click.echo("Bitte geben Sie eine Option an. Verwenden Sie --help für Hilfe.")

if __name__ == "__main__":
    main()