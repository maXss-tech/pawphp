-- Run ONLY if you already imported the old database.sql (new installs don't need this)
USE pawphp2;
ALTER TABLE bookings
  ADD COLUMN payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid' AFTER status,
  ADD COLUMN room_number VARCHAR(30) NULL AFTER payment_status,
  ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0 AFTER room_number,
  ADD INDEX (payment_status), ADD INDEX (archived);
