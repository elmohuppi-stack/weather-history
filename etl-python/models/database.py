"""
Datenbank-Modelle für Wetterdaten
"""

from sqlalchemy import (
    Column,
    Integer,
    String,
    Float,
    Date,
    Boolean,
    JSON,
    ForeignKey,
    PrimaryKeyConstraint,
)
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship
from datetime import date

Base = declarative_base()


class Station(Base):
    """Wetterstation"""

    __tablename__ = "stations"

    id = Column(String(10), primary_key=True)  # DWD Stations-ID (z.B. 01048)
    name = Column(String(100), nullable=False)
    lat = Column(Float, nullable=False)  # Breitengrad
    lon = Column(Float, nullable=False)  # Längengrad
    elevation = Column(Integer)  # Höhe über NN in Metern
    state = Column(String(50))  # Bundesland
    start_date = Column(Date)  # Erster verfügbarer Datensatz
    end_date = Column(Date)  # Letzter verfügbarer Datensatz
    active = Column(Boolean, default=True)  # Station aktiv?

    # Beziehungen
    measurements = relationship(
        "DailyMeasurement", back_populates="station", cascade="all, delete-orphan"
    )

    def __repr__(self):
        return f"<Station(id='{self.id}', name='{self.name}')>"


class DailyMeasurement(Base):
    """Tägliche Wettermessungen"""

    __tablename__ = "daily_measurements"

    station_id = Column(String(10), ForeignKey("stations.id"), primary_key=True)
    date = Column(Date, primary_key=True)

    # Temperaturwerte in °C
    temp_max = Column(Float)  # Tagesmaximum
    temp_min = Column(Float)  # Tagesminimum
    temp_mean = Column(Float)  # Tagesmittel

    # Niederschlag in mm
    precipitation = Column(Float)

    # Sonnenscheindauer in Stunden
    sunshine = Column(Float)

    # Schneehöhe in cm
    snow_depth = Column(Float)

    # Qualitätsflags als JSON
    quality_flags = Column(JSON)

    # Beziehungen
    station = relationship("Station", back_populates="measurements")

    def __repr__(self):
        return f"<DailyMeasurement(station='{self.station_id}', date='{self.date}')>"


class MonthlyAggregate(Base):
    """Monatliche Aggregationen für Performance"""

    __tablename__ = "monthly_aggregates"

    station_id = Column(String(10), ForeignKey("stations.id"), primary_key=True)
    year = Column(Integer, primary_key=True)
    month = Column(Integer, primary_key=True)  # 1-12

    # Aggregierte Werte
    temp_mean = Column(Float)  # Monatsmittel Temperatur
    temp_max_avg = Column(Float)  # Durchschnitt der Tagesmaxima
    temp_min_avg = Column(Float)  # Durchschnitt der Tagesminima
    precipitation_sum = Column(Float)  # Monatssumme Niederschlag
    sunshine_sum = Column(Float)  # Monatssumme Sonnenscheindauer
    snow_depth_max = Column(Float)  # Maximale Schneehöhe

    # Anzahl Tage mit Daten
    days_with_data = Column(Integer)

    def __repr__(self):
        return f"<MonthlyAggregate(station='{self.station_id}', {self.year}-{self.month:02d})>"


class ImportLog(Base):
    """Log für Datenimporte"""

    __tablename__ = "import_logs"

    id = Column(Integer, primary_key=True, autoincrement=True)
    timestamp = Column(Date, default=date.today, nullable=False)
    station_id = Column(String(10), ForeignKey("stations.id"))
    operation = Column(String(50), nullable=False)  # 'create', 'update', 'delete'
    records_processed = Column(Integer, default=0)
    success = Column(Boolean, default=True)
    error_message = Column(String(500))
    duration_seconds = Column(Float)

    def __repr__(self):
        return f"<ImportLog(id={self.id}, station='{self.station_id}', operation='{self.operation}')>"
