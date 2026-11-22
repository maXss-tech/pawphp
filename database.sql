-- PawBnB database (MySQL 5.7+ / MariaDB 10.3+)
CREATE DATABASE IF NOT EXISTS pawbnb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pawbnb;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  name       VARCHAR(80)  NOT NULL,
  type       ENUM('Dog','Cat','Other') NOT NULL,
  breed      VARCHAR(80)  NULL,
  age_years  DECIMAL(4,1) NULL,
  size_weight VARCHAR(60) NULL,
  vaccinated ENUM('Yes, up to date','Partially','No') NULL,
  notes      VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id)
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  pet_id      INT UNSIGNED NULL,
  destination VARCHAR(150) NOT NULL,
  pax         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  num_pets    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  pet_type    ENUM('Dog','Cat','Other') NOT NULL,
  pet_size    VARCHAR(60) NOT NULL,
  budget      VARCHAR(60) NULL,
  check_in    DATE NOT NULL,
  check_out   DATE NOT NULL,
  nights      SMALLINT UNSIGNED NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status      ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_book_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_book_pet  FOREIGN KEY (pet_id)  REFERENCES pets(id)  ON DELETE SET NULL,
  INDEX (user_id), INDEX (status), INDEX (check_in)
) ENGINE=InnoDB;

CREATE TABLE emergency_requests (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  name       VARCHAR(100) NOT NULL,
  contact    VARCHAR(40)  NOT NULL,
  location   VARCHAR(200) NOT NULL,
  pet_type   ENUM('Dog','Cat','Other') NOT NULL,
  situation  VARCHAR(500) NOT NULL,
  status     ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_emg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (status)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  title      VARCHAR(120) NOT NULL,
  body       VARCHAR(255) NOT NULL,
  href       VARCHAR(60)  NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_note_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE chat_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NULL,
  session_id VARCHAR(64)  NOT NULL,
  sender     ENUM('user','agent') NOT NULL,
  message    VARCHAR(1000) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_chat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (session_id)
) ENGINE=InnoDB;

-- To create an admin: sign up on the site, then run:
-- UPDATE users SET role='admin' WHERE email='you@example.com';
