-- Initiales Datenbank-Schema für Weather History DWD
-- PostgreSQL mit PostGIS und TimescaleDB

-- Enable PostGIS extension
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;

-- Enable TimescaleDB extension (falls installiert)
-- CREATE EXTENSION IF NOT EXISTS timescaledb;

-- Stations table
CREATE TABLE stations (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    lat DECIMAL(9,6) NOT NULL,
    lon DECIMAL(9,6) NOT NULL,
    elevation INTEGER,
    state VARCHAR(50),
    start_date DATE,
    end_date DATE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Daily measurements table (TimescaleDB hypertable)
CREATE TABLE daily_measurements (
    station_id VARCHAR(10) NOT NULL REFERENCES stations(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    
    -- Temperature values in °C
    temp_max DECIMAL(5,2),
    temp_min DECIMAL(5,2),
    temp_mean DECIMAL(5,2),
    
    -- Precipitation in mm
    precipitation DECIMAL(5,1),
    
    -- Sunshine duration in hours
    sunshine DECIMAL(4,1),
    
    -- Snow depth in cm
    snow_depth DECIMAL(4,1),
    
    -- Quality flags as JSON
    quality_flags JSONB,
    
    -- Metadata
    imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    source_file VARCHAR(255),
    
    PRIMARY KEY (station_id, date)
);

-- Convert to TimescaleDB hypertable for time-series optimization
-- SELECT create_hypertable('daily_measurements', 'date', if_not_exists => TRUE);

-- Monthly aggregates for performance
CREATE TABLE monthly_aggregates (
    station_id VARCHAR(10) NOT NULL REFERENCES stations(id) ON DELETE CASCADE,
    year INTEGER NOT NULL,
    month INTEGER NOT NULL CHECK (month >= 1 AND month <= 12),
    
    -- Aggregated values
    temp_mean DECIMAL(5,2),
    temp_max_avg DECIMAL(5,2),
    temp_min_avg DECIMAL(5,2),
    precipitation_sum DECIMAL(7,1),
    sunshine_sum DECIMAL(6,1),
    snow_depth_max DECIMAL(4,1),
    
    -- Statistics
    days_with_data INTEGER DEFAULT 0,
    
    -- Metadata
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (station_id, year, month)
);

-- Import logs
CREATE TABLE import_logs (
    id SERIAL PRIMARY KEY,
    timestamp DATE DEFAULT CURRENT_DATE,
    station_id VARCHAR(10) REFERENCES stations(id),
    operation VARCHAR(50) NOT NULL,
    records_processed INTEGER DEFAULT 0,
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    duration_seconds DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Spatial index for stations
CREATE INDEX idx_stations_location ON stations USING GIST (ST_SetSRID(ST_MakePoint(lon, lat), 4326));

-- Indexes for daily_measurements
CREATE INDEX idx_daily_measurements_date ON daily_measurements(date);
CREATE INDEX idx_daily_measurements_station_date ON daily_measurements(station_id, date);
CREATE INDEX idx_daily_measurements_temp_mean ON daily_measurements(temp_mean);
CREATE INDEX idx_daily_measurements_precipitation ON daily_measurements(precipitation);

-- Indexes for monthly_aggregates
CREATE INDEX idx_monthly_aggregates_station_year_month ON monthly_aggregates(station_id, year, month);
CREATE INDEX idx_monthly_aggregates_year_month ON monthly_aggregates(year, month);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger for stations table
CREATE TRIGGER update_stations_updated_at BEFORE UPDATE ON stations
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Function to calculate monthly aggregates
CREATE OR REPLACE FUNCTION calculate_monthly_aggregates(
    p_station_id VARCHAR(10),
    p_year INTEGER,
    p_month INTEGER
) RETURNS VOID AS $$
BEGIN
    INSERT INTO monthly_aggregates (
        station_id, year, month,
        temp_mean, temp_max_avg, temp_min_avg,
        precipitation_sum, sunshine_sum, snow_depth_max,
        days_with_data
    )
    SELECT
        p_station_id,
        p_year,
        p_month,
        AVG(temp_mean) as temp_mean,
        AVG(temp_max) as temp_max_avg,
        AVG(temp_min) as temp_min_avg,
        SUM(precipitation) as precipitation_sum,
        SUM(sunshine) as sunshine_sum,
        MAX(snow_depth) as snow_depth_max,
        COUNT(*) as days_with_data
    FROM daily_measurements
    WHERE station_id = p_station_id
        AND EXTRACT(YEAR FROM date) = p_year
        AND EXTRACT(MONTH FROM date) = p_month
        AND temp_mean IS NOT NULL
    ON CONFLICT (station_id, year, month) DO UPDATE SET
        temp_mean = EXCLUDED.temp_mean,
        temp_max_avg = EXCLUDED.temp_max_avg,
        temp_min_avg = EXCLUDED.temp_min_avg,
        precipitation_sum = EXCLUDED.precipitation_sum,
        sunshine_sum = EXCLUDED.sunshine_sum,
        snow_depth_max = EXCLUDED.snow_depth_max,
        days_with_data = EXCLUDED.days_with_data,
        calculated_at = CURRENT_TIMESTAMP;
END;
$$ LANGUAGE plpgsql;

-- View for station statistics
CREATE OR REPLACE VIEW station_statistics AS
SELECT
    s.id,
    s.name,
    s.state,
    COUNT(DISTINCT EXTRACT(YEAR FROM dm.date)) as years_available,
    MIN(dm.date) as first_date,
    MAX(dm.date) as last_date,
    COUNT(dm.date) as total_days,
    ROUND(AVG(dm.temp_mean)::numeric, 2) as avg_temp,
    ROUND(MAX(dm.temp_max)::numeric, 2) as max_temp,
    ROUND(MIN(dm.temp_min)::numeric, 2) as min_temp,
    ROUND(SUM(dm.precipitation)::numeric, 2) as total_precipitation
FROM stations s
LEFT JOIN daily_measurements dm ON s.id = dm.station_id
GROUP BY s.id, s.name, s.state;

-- Insert sample stations for development
INSERT INTO stations (id, name, lat, lon, elevation, state, start_date, end_date, active) VALUES
('01048', 'Berlin-Tempelhof', 52.467, 13.400, 48, 'Berlin', '1934-01-01', '2024-12-31', true),
('01358', 'Hamburg-Fuhlsbüttel', 53.633, 10.000, 16, 'Hamburg', '1890-08-01', '2024-12-31', true),
('01050', 'München-Stadt', 48.140, 11.570, 515, 'Bayern', '1949-01-01', '2024-12-31', true),
('01270', 'Köln-Bonn', 50.867, 7.167, 91, 'Nordrhein-Westfalen', '1951-01-01', '2024-12-31', true),
('01420', 'Frankfurt/Main', 50.050, 8.600, 112, 'Hessen', '1935-07-01', '2024-12-31', true)
ON CONFLICT (id) DO NOTHING;

-- Create read-only user for API access (optional)
-- CREATE USER api_user WITH PASSWORD 'api_password';
-- GRANT CONNECT ON DATABASE weather_history TO api_user;
-- GRANT SELECT ON ALL TABLES IN SCHEMA public TO api_user;
-- GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO api_user;