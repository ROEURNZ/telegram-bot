  
CREATE DATABASE IF NOT EXISTS tlbarcode_dev;
USE tlbarcode_dev;



CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT NOT NULL UNIQUE, 
  `chat_id` BIGINT NOT NULL,
  `msg_id` BIGINT NOT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) DEFAULT NULL,
  `username` VARCHAR(255) DEFAULT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `date` DATETIME NOT NULL,
  `language` ENUM('en', 'kh') NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;




CREATE TABLE IF NOT EXISTS `decode_bar_qrcode` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `code` TEXT NOT NULL,
  `msg_id` bigint DEFAULT NULL,
  `file_id` varchar(100) NOT NULL,
  `file_unique_id` varchar(100) NOT NULL,
  `lat` VARCHAR(50) NULL,
  `lon` VARCHAR(50) NULL,
  `location_url` VARCHAR(255) NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `decoded_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ocr_tax_invoice` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL,
    `tin` VARCHAR(50) NULL,
    `lat` VARCHAR(50) NULL,
    `lon` VARCHAR(50) NULL,
    `location_url` VARCHAR(255) NULL,
    `ocrtext` BIT NULL,
    `msg_id` BIGINT NOT NULL, 
    `raw_data` TEXT NULL,
    `file_id` varchar(100) NOT NULL,
    `ocrhasvat` BIT NULL,
    `taxincluded` BIT NULL,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- CREATE TABLE IF NOT EXISTS `location` (
--   `id` int NOT NULL AUTO_INCREMENT,
--   `user_id` bigint NOT NULL,
--   `lat` varchar(50) NOT NULL,
--   `lon` varchar(50) NOT NULL,
--   `location_url` varchar(255) DEFAULT NULL,
--   `date` datetime DEFAULT CURRENT_TIMESTAMP,
--   `share_status` tinyint(1) DEFAULT '0',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `mrz_uic` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL,
    `file_id` VARCHAR(100) NOT NULL,
    `msg_id` BIGINT NOT NULL,
    `mrz_raw` TEXT NULL,
    `uic_data` TEXT NULL,
    `lat` VARCHAR(50) NULL,
    `lon` VARCHAR(50) NULL,
    `location_url` VARCHAR(255) NULL,
    `mrz_status` TINYINT(1) NOT NULL DEFAULT 0,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user_profiles` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;


