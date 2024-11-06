-- Host: localhost
-- Generation Time: October 10, 2024 at 3:24 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.3.29

CREATE DATABASE IF NOT EXISTS  ezzebarcodedev;
USE IF EXISTS  ezzebarcodedev;

CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `chat_id` bigint NOT NULL,
  `msg_id` bigint NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date` datetime NOT NULL,
  `language` enum('en','kh') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `barcodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `msg_id` bigint DEFAULT NULL,
  `file_id` varchar(100) NOT NULL,
  `file_unique_id` varchar(100) NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `decoded_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS ocr (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL,
    `msg_id` BIGINT NOT NULL,
    `raw_data`	VARCHAR(100) NULL,
    `file_id` varchar(100) NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 0,
    `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ocrtext` BIT NULL,
    `ocrhasvat` BIT NULL,
    `taxincluded` BIT NULL,
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `location` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `lat` varchar(50) NOT NULL,
  `lon` varchar(50) NOT NULL,
  `location_url` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `share_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;



