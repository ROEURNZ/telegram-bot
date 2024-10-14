-- Host: localhost
-- Generation Time: October 10, 2024 at 3:24 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "Asia/Phnom_Penh";

-- Create the ezzebot database
CREATE DATABASE IF NOT EXISTS ezzebot;
USE ezzebot;



-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    chat_id BIGINT NOT NULL,
    msg_id BIGINT NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255),
    username VARCHAR(255),
    phone_number VARCHAR(20),
    date DATETIME NOT NULL,
    language ENUM('en', 'kh') NOT NULL,
    PRIMARY KEY (id)
);

-- Create the barcode table
CREATE TABLE IF NOT EXISTS decoded (
    id INT NOT NULL AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type VARCHAR(50) NULL,
    code VARCHAR(255) NOT NULL,
    msg_id BIGINT NULL,
    file_id VARCHAR(100) NOT NULL,
    file_unique_id VARCHAR(100) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Create the location table
CREATE TABLE IF NOT EXISTS location (
    id INT NOT NULL AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    lat VARCHAR(50) NOT NULL,
    lon VARCHAR(50) NOT NULL,
    location_url VARCHAR(255) NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

COMMIT;
