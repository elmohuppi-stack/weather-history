#!/usr/bin/env python3
"""
Überprüfe den Status der Station-Importe
"""
import sys
import os
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent))

from etl_python.models.database import Station, DailyMeasurement
from etl_python.config.settings import DATABASE_URL, SELECTED_STATIONS
from sqlalchemy import create_engine, func
from sqlalchemy.orm import sessionmaker
from datetime import datetime


def check_import_status():
    """Überprüfe Importstatus aller Stationen"""
    engine = create_engine(DATABASE_URL, echo=False)
    Session = sessionmaker(bind=engine)
    session = Session()

    print("\n" + "=" * 80)
    print("STATION IMPORT STATUS")
    print("=" * 80)

    total_records = 0

    for station_id in SELECTED_STATIONS:
        try:
            # Versuche Station zu finden
            station = session.query(Station).filter_by(id=station_id).first()

            # Zähle Datensätze für diese Station
            count = (
                session.query(func.count(DailyMeasurement.date))
                .filter_by(station_id=station_id)
                .scalar()
                or 0
            )
            total_records += count

            if station:
                status = "✓"
                print(
                    f"{status} {station_id} | {station.name:40s} | {count:6,d} records | {station.start_date} to {station.end_date}"
                )
            else:
                print(f"⚠ {station_id} | (Station not in DB yet) | {count:6,d} records")

        except Exception as e:
            print(f"✗ {station_id} | ERROR: {str(e)[:60]}")

    print("=" * 80)
    print(
        f"TOTAL: {len(SELECTED_STATIONS)} stations | {total_records:,d} total measurements"
    )
    print("=" * 80 + "\n")

    session.close()


if __name__ == "__main__":
    check_import_status()
