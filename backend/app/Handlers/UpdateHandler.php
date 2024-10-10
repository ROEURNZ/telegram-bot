<?php
// -- Users Table
// CREATE TABLE users (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     chat_id BIGINT UNIQUE,
//     first_name VARCHAR(100),
//     last_name VARCHAR(100),
//     phone_number VARCHAR(20),
//     language VARCHAR(10),
//     username VARCHAR(255),
//     contact_shared BOOLEAN DEFAULT 0,
//     started BOOLEAN DEFAULT 0,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
// );

// -- Decoded Barcodes Table
// CREATE TABLE decoded_barcodes (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT,
//     barcode_data TEXT,
//     image_path VARCHAR(255),
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
// );

// -- Locations Table
// CREATE TABLE locations (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT,
//     latitude DECIMAL(10, 8),
//     longitude DECIMAL(11, 8),
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
// );
