<?php
/* @ROEURNZ=> File name & directory
 * backend/app/Config/database.php
 * 
 */

 /*
  * @Database host
  * @Database username
  * @Database password
  * @Database name
  * @Database character set
  * @Database collation
  * @Table prefix if any
  */
return [
    'host' => getenv('DB_HOST') ?: 'localhost', 
    'username' => getenv('DB_USERNAME') ?: 'root', 
    'password' => getenv('DB_PASSWORD') ?: '7560', 
    'database' => getenv('DB_DATABASE') ?: 'php_telegram_bot', 
    'charset' => 'utf8mb4', 
    'collation' => 'utf8mb4_unicode_ci', 
    'prefix' => '', 
];
