<?php
/* @ROEURNZ=> File name & directory
 * backend/app/Models/EzzeModel.php
 * 
 */

// Include db.php to get the PDO instance
$pdo = require_once __DIR__ . '/../Config/db.php';


class EzzeModels
{
    private $pdo;

    // Use the singleton instance from db.php
    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function tgUsername($userId)
    {
        // Prepare the SQL statement to fetch the username by user ID
        $sql = "SELECT username FROM `users` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
    
        // Execute the statement with the provided user ID
        $stmt->execute([':user_id' => $userId]);
    
        // Fetch the username; returns username or null if not found
        return $stmt->fetchColumn() ?: null;
    }
}
