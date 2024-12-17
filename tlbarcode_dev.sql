CREATE DATABASE IF NOT EXISTS tlbarcode_dev;

USE tlbarcode_dev;

CREATE TABLE `decode_bar_qrcode` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `code` text NOT NULL,
  `msg_id` bigint DEFAULT NULL,
  `file_id` varchar(100) NOT NULL,
  `file_unique_id` varchar(100) NOT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `location_url` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `decoded_status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `mrz_uic` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `file_id` varchar(100) NOT NULL,
  `msg_id` bigint NOT NULL,
  `mrz_raw` text,
  `uic_data` text,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `location_url` varchar(255) DEFAULT NULL,
  `mrz_status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `ocr_tax_invoice` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `location_url` varchar(255) DEFAULT NULL,
  `ocrtext` bit(1) DEFAULT NULL,
  `msg_id` bigint NOT NULL,
  `raw_data` text,
  `file_id` varchar(100) NOT NULL,
  `ocrhasvat` bit(1) DEFAULT NULL,
  `taxincluded` bit(1) DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `user_profiles` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `chat_id` bigint NOT NULL,
  `msg_id` bigint NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `permission` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date` datetime NOT NULL,
  `language` enum('en','kh') NOT NULL DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `decode_bar_qrcode`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mrz_uic`
--
ALTER TABLE `mrz_uic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ocr_tax_invoice`
--
ALTER TABLE `ocr_tax_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `decode_bar_qrcode`
--
ALTER TABLE `decode_bar_qrcode`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `mrz_uic`
--
ALTER TABLE `mrz_uic`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `ocr_tax_invoice`
--
ALTER TABLE `ocr_tax_invoice`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mrz_uic`
--
ALTER TABLE `mrz_uic`
  ADD CONSTRAINT `mrz_uic_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_profiles` (`user_id`) ON DELETE CASCADE;
COMMIT;
