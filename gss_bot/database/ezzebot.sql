CREATE DATABASE gss_etrax_bot;

USE gss_etrax_bot;



SET
  SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
  time_zone = "+00:00";

--
-- Table structure for table `bot_admin`
--
CREATE TABLE
  `bot_admin` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `admin_name` varchar(100) CHARACTER
    SET
      utf8 DEFAULT NULL,
      `step` varchar(100) CHARACTER
    SET
      utf8 DEFAULT NULL,
      `temp` text CHARACTER
    SET
      utf8 DEFAULT NULL,
      PRIMARY KEY (`id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
--

-- Table structure for table `bot_cron`
--
CREATE TABLE
  `bot_cron` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `title` varchar(100) NOT NULL,
    `cron_file` varchar(50) NOT NULL,
    `cron_command` varchar(200) NOT NULL,
    `cron_config` text NOT NULL,
    `cron_active` tinyint (1) NOT NULL DEFAULT 1,
    `last_run` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;



-- --------------------------------------------------------
--
-- Table structure for table `bot_settings`
--
CREATE TABLE
  `bot_settings` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `time_tolerance` int (11) DEFAULT NULL,
    `location_tolerance` decimal(10, 3) NOT NULL,
    `userbreak_req_step` int (1) NOT NULL,
    `clockuser_req_step` int (1) NOT NULL,
    `dead_man_feature` tinyint (1) NOT NULL DEFAULT 0,
    `dead_man_task_time` int (3) NOT NULL DEFAULT 30,
    `welcome_msg` text NOT NULL,
    `welcome_img` varchar(100) NOT NULL,
    `company_email` varchar(50) NOT NULL,
    `company_phone` varchar(30) NOT NULL,
    `module_visit` tinyint (1) NOT NULL DEFAULT 0,
    `module_alert` tinyint (1) NOT NULL DEFAULT 0,
    `module_break` tinyint (1) NOT NULL DEFAULT 0,
    `clockout_reminder_interval` int (3) NOT NULL,
    `clockout_reminder_timeout` int (2) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- --------------------------------------------------------
--
-- Table structure for table `branch`
--
CREATE TABLE
  `branch` (
    `branch_id` int (11) NOT NULL AUTO_INCREMENT,
    `branch_name` varchar(100) DEFAULT NULL,
    `branch_lat` varchar(100) DEFAULT NULL,
    `branch_lon` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`branch_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `reminder`
--
CREATE TABLE
  `reminder` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `type` varchar(30) NOT NULL,
    `start_time` datetime NOT NULL,
    `end_time` datetime NOT NULL,
    `sent` tinyint (1) NOT NULL DEFAULT 0,
    `reply` tinyint (1) NOT NULL DEFAULT 0,
    `response` varchar(30) DEFAULT NULL,
    `reminder_msg` text DEFAULT NULL,
    `reminder_button` text DEFAULT NULL,
    `reminder_num` int (2) NOT NULL DEFAULT 1,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `request_reply_log`
--
CREATE TABLE
  `request_reply_log` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `user_request` text DEFAULT NULL,
    `bot_reply` text DEFAULT NULL,
    `api_request_url` text DEFAULT NULL,
    `api_response` text DEFAULT NULL,
    `wrong_reply_user_stat` text DEFAULT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `scheduled_messages`
--
CREATE TABLE
  `scheduled_messages` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `title` varchar(200) NOT NULL,
    `message` text NOT NULL,
    `destination` text NOT NULL,
    `media_type` varchar(20) NOT NULL,
    `media` varchar(200) NOT NULL,
    `runtime` tinyint (1) NOT NULL,
    `last_run` datetime NOT NULL,
    `created_by` bigint (20) NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `scheduled_messages_time`
--
CREATE TABLE
  `scheduled_messages_time` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `message_id` int (10) NOT NULL,
    `day` varchar(10) NOT NULL,
    `time` varchar(10) NOT NULL,
    `is_run` tinyint (1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `user_break`
--
CREATE TABLE
  `user_break` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `break_day` varchar(10) CHARACTER
    SET
      utf8 NOT NULL,
      `break_time` time NOT NULL,
      `location_status` varchar(20) CHARACTER
    SET
      utf8 NOT NULL,
      `location_lat` varchar(20) CHARACTER
    SET
      utf8 NOT NULL,
      `location_lon` varchar(20) CHARACTER
    SET
      utf8 NOT NULL,
      `location_msg_id` int (11) NOT NULL,
      `location_distance` varchar(20) CHARACTER
    SET
      utf8 NOT NULL,
      `selfie_msg_id` varchar(100) CHARACTER
    SET
      utf8 NOT NULL,
      `break_action` varchar(15) CHARACTER
    SET
      utf8 NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = latin1;

-- --------------------------------------------------------
--
-- Table structure for table `user_clock_in_out`
--
CREATE TABLE
  `user_clock_in_out` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `clock_in_day` varchar(10) DEFAULT NULL,
    `clock_in_location_status` varchar(255) DEFAULT NULL,
    `clock_in_lat` varchar(100) DEFAULT NULL,
    `clock_in_lon` varchar(100) DEFAULT NULL,
    `clock_in_location_msg_id` int (11) DEFAULT NULL,
    `clock_in_distance` varchar(55) DEFAULT NULL,
    `clock_in_time_status` varchar(255) DEFAULT NULL,
    `clock_in_time` time DEFAULT NULL,
    `work_start_time` time DEFAULT NULL,
    `clock_in_selfie_msg_id` varchar(100) DEFAULT NULL,
    `is_clock_in` varchar(100) DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `user_daily_tasks`
--
CREATE TABLE
  `user_daily_tasks` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `task_start` datetime NOT NULL,
    `task_end` datetime NOT NULL,
    `task_send` tinyint (1) NOT NULL DEFAULT 0,
    `task_reply` tinyint (1) NOT NULL DEFAULT 0,
    `task_status` varchar(25) NOT NULL,
    `reply_time` varchar(30) DEFAULT NULL,
    `reply_location` varchar(100) DEFAULT NULL,
    `reply_location_status` varchar(25) DEFAULT NULL,
    `reply_location_distance` varchar(10) DEFAULT NULL,
    `reply_location_msg_id` int (10) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `user_profiles`
--
CREATE TABLE
  `user_profiles` (
    `id` bigint (20) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `firstname` varchar(50) DEFAULT NULL,
    `lastname` varchar(50) DEFAULT NULL,
    `tg_username` varchar(50) DEFAULT NULL,
    `phone` varchar(50) DEFAULT NULL,
    `email` varchar(50) DEFAULT NULL,
    `approval_status` varchar(100) DEFAULT NULL,
    `notification_new_user_msg_id` bigint (20) DEFAULT NULL,
    `list_emp_msg_id` int (11) DEFAULT NULL,
    `photo_message_id` bigint (20) DEFAULT NULL,
    `photo_id` varchar(200) DEFAULT NULL,
    `day_selected_msg_id` bigint (20) DEFAULT NULL,
    `set_start_time_msg_id` bigint (20) DEFAULT NULL,
    `set_end_time_msg_id` bigint (20) DEFAULT NULL,
    `branch_id` int (11) DEFAULT NULL,
    `branch_name` varchar(255) DEFAULT NULL,
    `step` varchar(50) DEFAULT NULL,
    `is_step_complete` tinyint (1) NOT NULL DEFAULT 1,
    `lang` enum ('en', 'kh') DEFAULT 'en',
    `trigger_alarm` tinyint (1) NOT NULL DEFAULT 0,
    `jobdesc` varchar(100) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `can_break` tinyint (1) NOT NULL DEFAULT 0,
    `break_step` tinyint (1) NOT NULL DEFAULT 0,
    `can_visit` tinyint (1) NOT NULL DEFAULT 0,
    `visit_alert` tinyint (1) NOT NULL DEFAULT 0,
    `ping_module` tinyint (1) NOT NULL DEFAULT 0,
    `approved_by` bigint (20) NOT NULL DEFAULT 0,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------
--
-- Table structure for table `user_visits`
--
CREATE TABLE
  `user_visits` (
    `id` int (10) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `visit_day` varchar(10) NOT NULL,
    `visit_time` datetime NOT NULL,
    `visit_lat` varchar(20) NOT NULL,
    `visit_lon` varchar(20) NOT NULL,
    `visit_location_msg_id` int (10) NOT NULL,
    `visit_selfie_msg_id` varchar(100) NOT NULL,
    `visit_notes` text NOT NULL,
    `visit_action` varchar(15) NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
--
-- Table structure for table `user_working_hour`
--
CREATE TABLE
  `user_working_hour` (
    `id` bigint (20) NOT NULL AUTO_INCREMENT,
    `user_id` bigint (20) NOT NULL,
    `work_day` varchar(255) NOT NULL,
    `start_time` varchar(8) DEFAULT NULL,
    `end_time` varchar(8) DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;