CREATE TABLE IF NOT EXISTS sensors (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  source_url VARCHAR(1000),
  source_type ENUM('BMKG_API','HTML_SCRAPE') NOT NULL DEFAULT 'BMKG_API',
  external_id VARCHAR(255),
  scrape_config JSON NULL,
  last_success_at DATETIME NULL,
  status ENUM('OK','ERROR','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- sensor_readings
CREATE TABLE IF NOT EXISTS sensor_readings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sensor_id BIGINT NOT NULL,
  reading_time DATETIME NOT NULL,
  temperature DECIMAL(6,2) NULL,
  humidity DECIMAL(6,2) NULL,
  wind_speed DECIMAL(8,2) NULL,
  raw_payload JSON NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(sensor_id),
  FOREIGN KEY(sensor_id) REFERENCES sensors(id) ON DELETE CASCADE
);

-- scrape_errors
CREATE TABLE IF NOT EXISTS scrape_errors (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sensor_id BIGINT NULL,
  source_url VARCHAR(1000),
  error_message TEXT,
  http_status INT NULL,
  occurred_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  handled BOOLEAN DEFAULT FALSE,
  FOREIGN KEY(sensor_id) REFERENCES sensors(id) ON DELETE SET NULL
);

-- sensor_thresholds
CREATE TABLE IF NOT EXISTS sensor_thresholds (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sensor_id BIGINT NOT NULL,
  field_name VARCHAR(50) NOT NULL,
  operator ENUM('>','<','>=','<=','=','!=') NOT NULL,
  value DECIMAL(10,4) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(sensor_id) REFERENCES sensors(id) ON DELETE CASCADE
);

-- alerts
CREATE TABLE IF NOT EXISTS alerts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sensor_id BIGINT NOT NULL,
  field_name VARCHAR(50) NOT NULL,
  value DECIMAL(10,4) NOT NULL,
  threshold_value DECIMAL(10,4) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- BMKG: prakiraan cuaca
CREATE TABLE IF NOT EXISTS prakiraan_cuaca (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  adm4 VARCHAR(50) NOT NULL,
  desa VARCHAR(255) NULL,
  cuaca VARCHAR(255) NULL,
  suhu_min DECIMAL(6,2) NULL,
  suhu_max DECIMAL(6,2) NULL,
  wind_speed DECIMAL(8,2) NULL,
  arah_angin VARCHAR(50) NULL,
  waktu_pembaruan DATETIME NULL,
  sumber VARCHAR(100) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- BMKG: nowcast alerts
CREATE TABLE IF NOT EXISTS nowcast_alerts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  adm4 VARCHAR(50) NOT NULL,
  desa VARCHAR(255) NULL,
  peringatan TEXT NULL,
  level VARCHAR(50) NULL,
  wilayah_kritis BOOLEAN NULL,
  waktu_pembaruan DATETIME NULL,
  sumber VARCHAR(100) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
