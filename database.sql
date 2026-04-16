-- ============================================
--  OR — On Rent  |  Database Schema
--  Import this in phpMyAdmin or MySQL CLI
-- ============================================

CREATE DATABASE IF NOT EXISTS or_onrent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE or_onrent;

-- ── OWNERS TABLE ──
CREATE TABLE IF NOT EXISTS owners (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  email       VARCHAR(180) NOT NULL UNIQUE,
  mobile      VARCHAR(20)  NOT NULL,
  password    VARCHAR(255) NOT NULL,
  city        VARCHAR(80)  NOT NULL,
  area        VARCHAR(100) NOT NULL,
  kyc_doc     VARCHAR(255) DEFAULT NULL COMMENT 'Aadhaar / License path',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── ADMINS TABLE ──
CREATE TABLE IF NOT EXISTS admins (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(80)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin, password=admin123 (change immediately!)
INSERT INTO admins (username, password)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE id=id;

-- ── LISTINGS TABLE ──
CREATE TABLE IF NOT EXISTS listings (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  owner_id         INT NOT NULL,
  category         ENUM('Labour & Services','Vehicles & Services','Marriage & Services') NOT NULL,
  type             VARCHAR(120) NOT NULL COMMENT 'e.g. Electrician, Honda City, Caterer',
  price            DECIMAL(10,2) NOT NULL,
  pricing_type     ENUM('Half Day','Full Day','Hourly','Per KM / Distance','Per Plate','Per Event') NOT NULL,
  description      TEXT,
  image            VARCHAR(255) DEFAULT NULL,
  city             VARCHAR(80) NOT NULL,
  area             VARCHAR(100) NOT NULL,
  driver_included  TINYINT(1) DEFAULT 0,
  status           ENUM('pending','approved','rejected') DEFAULT 'pending',
  date_added       DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── USERS (CUSTOMERS) TABLE ──
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  email       VARCHAR(180) NOT NULL UNIQUE,
  mobile      VARCHAR(20)  NOT NULL,
  password    VARCHAR(255) NOT NULL,
  city        VARCHAR(80),
  area        VARCHAR(100),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── BOOKINGS TABLE ──
CREATE TABLE IF NOT EXISTS bookings (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT DEFAULT NULL,
  listing_id     INT NOT NULL,
  customer_name  VARCHAR(120),
  customer_phone VARCHAR(20),
  booking_date   DATE NOT NULL,
  duration       VARCHAR(50),
  address        TEXT,
  payment_method ENUM('Razorpay','Paytm','Stripe','Cash') DEFAULT 'Razorpay',
  payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  payment_id     VARCHAR(120) DEFAULT NULL COMMENT 'Gateway transaction ID',
  amount         DECIMAL(10,2) DEFAULT 0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── REVIEWS TABLE ──
CREATE TABLE IF NOT EXISTS reviews (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  listing_id  INT NOT NULL,
  user_id     INT DEFAULT NULL,
  reviewer    VARCHAR(120),
  rating      TINYINT(1) CHECK (rating BETWEEN 1 AND 5),
  comment     TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Sample Data (optional for testing)
-- ============================================
-- INSERT INTO owners (name,email,mobile,password,city,area)
-- VALUES ('Rajesh Kumar','rajesh@test.com','9876543210',
--         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Pune','Pimpri');
