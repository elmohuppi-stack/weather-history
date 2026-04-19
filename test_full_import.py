#!/usr/bin/env python3
"""
Testet den vollständigen DWD-Import mit Datenbank
"""

import sys
import os
sys.path.append('etl-python')

from scripts.dwd_importer import DWDImporter
import pandas as pd

def test_database_import():
    """Testet den Import in die Datenbank"""
    print("=== Teste Datenbank-Import ===")
    
    # Erstelle Importer
    importer = DWDImporter()
    
    # Initialisiere Datenbank (falls nicht existiert)
    print("Initialisiere Datenbank...")
    try:
        importer.init_database()
        print("✅ Datenbank initialisiert")
    except Exception as e:
        print(f"⚠️  Datenbank-Initialisierung fehlgeschlagen (kann normal sein wenn bereits existiert): {e}")
    
    # Teste mit Berlin-Tempelhof (01048)
    station_id = '01048'
    print(f"\nImportiere Station {station_id} (Berlin-Tempelhof)...")
    
    try:
        # Importiere Station
        success = importer.import_station(station_id)
        
        if success:
            print(f"✅ Station {station_id} erfolgreich importiert")
            
            # Prüfe ob Daten in Datenbank sind
            session = importer.Session()
            
            # Zähle Stationen
            from models.database import Station, DailyMeasurement
            station_count = session.query(Station).filter_by(id=station_id).count()
            measurement_count = session.query(DailyMeasurement).filter_by(station_id=station_id).count()
            
            print(f"   Stationen in DB: {station_count}")
            print(f"   Messungen in DB: {measurement_count}")
            
            if measurement_count > 0:
                # Zeige einige Messungen
                measurements = session.query(DailyMeasurement).filter_by(station_id=station_id).order_by(DailyMeasurement.date.desc()).limit(5).all()
                print(f"\n   Letzte 5 Messungen:")
                for m in measurements:
                    print(f"     {m.date}: {m.temp_mean}°C, {m.precipitation}mm, {m.sunshine}h")
            
            session.close()
            return True
        else:
            print(f"❌ Import von Station {station_id} fehlgeschlagen")
            return False
            
    except Exception as e:
        print(f"❌ Fehler beim Import: {e}")
        import traceback
        traceback.print_exc()
        return False

def test_multiple_stations_import():
    """Testet Import mehrerer Stationen"""
    print("\n=== Teste Import mehrerer Stationen ===")
    
    importer = DWDImporter()
    test_stations = ['01048', '01358', '01050']  # Berlin, Hamburg, München
    
    success_count = 0
    for station_id in test_stations:
        print(f"\n--- Importiere Station {station_id} ---")
        try:
            success = importer.import_station(station_id)
            if success:
                print(f"✅ Erfolgreich importiert")
                success_count += 1
            else:
                print(f"❌ Import fehlgeschlagen")
        except Exception as e:
            print(f"❌ Fehler: {e}")
    
    print(f"\nErgebnis: {success_count}/{len(test_stations)} Stationen erfolgreich importiert")
    return success_count

def test_import_all_selected():
    """Testet den Import aller ausgewählten Stationen"""
    print("\n=== Teste Import aller ausgewählten Stationen ===")
    
    importer = DWDImporter()
    
    # Initialisiere Datenbank
    print("Initialisiere Datenbank...")
    try:
        importer.init_database()
        print("✅ Datenbank initialisiert")
    except Exception as e:
        print(f"⚠️  Datenbank-Initialisierung: {e}")
    
    # Importiere alle ausgewählten Stationen
    print(f"\nImportiere {len(importer.selected_stations)} ausgewählte Stationen...")
    
    try:
        success_count = importer.import_all_selected()
        print(f"\nErgebnis: {success_count}/{len(importer.selected_stations)} Stationen erfolgreich importiert")
        
        # Zeige Statistiken
        session = importer.Session()
        from models.database import Station, DailyMeasurement
        
        total_stations = session.query(Station).count()
        total_measurements = session.query(DailyMeasurement).count()
        
        print(f"\nDatenbank-Statistiken:")
        print(f"  Gesamte Stationen: {total_stations}")
        print(f"  Gesamte Messungen: {total_measurements}")
        
        if total_stations > 0:
            print(f"\nImportierte Stationen:")
            stations = session.query(Station).all()
            for s in stations:
                station_measurements = session.query(DailyMeasurement).filter_by(station_id=s.id).count()
                print(f"  {s.id}: {s.name} ({station_measurements} Messungen)")
        
        session.close()
        return success_count
        
    except Exception as e:
        print(f"❌ Fehler beim Import aller Stationen: {e}")
        import traceback
        traceback.print_exc()
        return 0

if __name__ == "__main__":
    print("DWD Full Import Test")
    print("=" * 60)
    
    # Teste einzelnen Import
    print("\n1. Teste einzelnen Stations-Import:")
    test_database_import()
    
    # Teste mehrere Stationen
    print("\n2. Teste Import mehrerer Stationen:")
    test_multiple_stations_import()
    
    # Frage ob alle Stationen getestet werden sollen
    print("\n" + "=" * 60)
    response = input("Möchtest du alle 16 ausgewählten Stationen importieren? (j/n): ")
    
    if response.lower() in ['j', 'ja', 'y', 'yes']:
        print("\n3. Teste Import aller 16 ausgewählten Stationen:")
        test_import_all_selected()
    
    print("\n" + "=" * 60)
    print("Test abgeschlossen")