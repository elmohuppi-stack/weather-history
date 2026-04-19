#!/usr/bin/env python3
"""
Testskript für direkten DWD-Download
"""

import sys
import os
sys.path.append('etl-python')

from scripts.dwd_importer import DWDImporter
import pandas as pd

def test_dwd_download():
    """Testet den direkten DWD-Download"""
    print("=== Teste direkten DWD-Download ===")
    
    # Erstelle Importer (ohne Datenbank-Verbindung für Test)
    importer = DWDImporter()
    
    # Teste mit Berlin-Tempelhof (01048)
    station_id = '01048'
    print(f"\nTeste Download für Station {station_id} (Berlin-Tempelhof)...")
    
    try:
        # Versuche direkten Download
        data = importer._download_real_dwd_data(station_id)
        
        if data is not None and not data.empty:
            print(f"✅ Erfolg! {len(data)} Datensätze geladen")
            print(f"\nErste 5 Zeilen:")
            print(data.head())
            print(f"\nSpalten: {list(data.columns)}")
            print(f"\nZeitraum: {data['date'].min()} bis {data['date'].max()}")
            print(f"\nDatenbeispiele:")
            print(f"  Temperatur (max/min/mean): {data['temp_max'].mean():.1f}°C / {data['temp_min'].mean():.1f}°C / {data['temp_mean'].mean():.1f}°C")
            print(f"  Niederschlag: {data['precipitation'].mean():.1f} mm")
            print(f"  Sonnenschein: {data['sunshine'].mean():.1f} h")
            
            # Prüfe auf Missing Values
            missing_counts = data.isnull().sum()
            if missing_counts.sum() > 0:
                print(f"\n⚠️  Missing Values gefunden:")
                for col, count in missing_counts.items():
                    if count > 0:
                        print(f"  {col}: {count} ({count/len(data)*100:.1f}%)")
            else:
                print(f"\n✅ Keine Missing Values gefunden")
                
            return True
        else:
            print(f"❌ Keine Daten für Station {station_id} gefunden")
            return False
            
    except Exception as e:
        print(f"❌ Fehler beim Download: {e}")
        import traceback
        traceback.print_exc()
        return False

def test_multiple_stations():
    """Testet mehrere Stationen"""
    print("\n=== Teste mehrere Stationen ===")
    
    importer = DWDImporter()
    test_stations = ['01048', '01358', '01050']  # Berlin, Hamburg, München
    
    for station_id in test_stations:
        print(f"\n--- Station {station_id} ---")
        try:
            data = importer._download_real_dwd_data(station_id)
            if data is not None and not data.empty:
                print(f"✅ {len(data)} Datensätze geladen")
                print(f"   Zeitraum: {data['date'].min().date()} bis {data['date'].max().date()}")
            else:
                print(f"❌ Keine Daten gefunden")
        except Exception as e:
            print(f"❌ Fehler: {e}")

if __name__ == "__main__":
    print("DWD Direct Download Test")
    print("=" * 50)
    
    # Teste einzelne Station
    success = test_dwd_download()
    
    if success:
        # Teste mehrere Stationen
        test_multiple_stations()
    
    print("\n" + "=" * 50)
    print("Test abgeschlossen")