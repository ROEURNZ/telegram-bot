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

    function addUser($params)
    {
        // SQL statement for inserting a new user
        $sql = "INSERT INTO `users` (user_id, chat_id, msg_id, first_name, last_name, username, phone_number, created_at, date, language) 
                VALUES (:user_id, :chat_id, :msg_id, :first_name, :last_name, :username, :phone_number, NOW(), :date, :language)";

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        // Execute the statement with parameters
        return $stmt->execute([
            ':user_id' => $params['user_id'],
            ':chat_id' => $params['chat_id'],
            ':msg_id' => $params['msg_id'],
            ':first_name' => $params['first_name'],
            ':last_name' => $params['last_name'],
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number'],
            ':date' => $params['date'],
            ':language' => $params['language']
        ]) ? "Record inserted successfully." : "Error: " . $stmt->errorInfo()[2];
    }



// Function to check if the user has selected a language
public function hasSelectedLanguage($userId)
{
    // Prepare the SQL query to check if the user has a language set
    $sql = "SELECT language FROM users WHERE user_id = :user_id LIMIT 1";
    $stmt = $this->pdo->prepare($sql);

    // Bind the user_id parameter
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the language value
    $language = $stmt->fetchColumn();

    // Return true if a language is set, otherwise false
    return !empty($language);
}


    public function getUserLanguage($chatId, $username)
    {
        // Prepare a statement to get the user's language from the database
        $stmt = $this->pdo->prepare("SELECT language FROM users WHERE chat_id = :chat_id AND username = :username");
        $stmt->execute(['chat_id' => $chatId, 'username' => $username]);

        // Fetch the language preference
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the language, or a default value if not found
        return $result ? $result['language'] : 'en'; // default to 'en' if not found
    }


    // Method to update user's language
    public function updateUserLanguage($chatId, $language)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET language = :language WHERE chat_id = :chat_id");
        return $stmt->execute(['language' => $language, 'chat_id' => $chatId]);
    }


    public function checkUserExists($userId)
    {
        return $this->isUserIdExists($userId);
    }

    private function isUserIdExists($userId)
    {
        // Check if the user already exists in the database
        $sql = "SELECT COUNT(*) FROM `users` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
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

    
    function updateTgUsername($userId, $username)
    {
        if (empty($userId)) {
            return false;
        }

        $sql = "UPDATE user_profiles SET username = :username WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':username' => $username, ':user_id' => $userId]);
    }


    // Check if the user exists by chatId
    public function isUserChatIdExists($chatId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `users` WHERE chat_id = :chat_id");
        $stmt->execute([':chat_id' => $chatId]);
        return $stmt->fetchColumn() > 0;
    }

    /** ----------------------------------------Contact sharing for register ----------------------------------------------- */

    // Function to add a barcode record
    public function addBarcode($params)
    {
        // Check if the barcode already exists
        if ($this->barcodeExists($params['code'])) {
            return "Error: Barcode already exists.";
        }

        // If the barcode doesn't exist, proceed with the insert
        $sql = "INSERT INTO barcode (user_id, type, code, msg_id, file_id, file_unique_id, decoded_status) 
            VALUES (:user_id, :type, :code, :msg_id, :file_id, :file_unique_id, :decoded_status)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $params['type'], PDO::PARAM_STR);
        $stmt->bindParam(':code', $params['code'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':file_unique_id', $params['file_unique_id'], PDO::PARAM_STR);
        $stmt->bindParam(':decoded_status', $params['decoded_status'], PDO::PARAM_INT);

        return $stmt->execute() ? "Barcode record inserted successfully." : "Error: " . $stmt->errorInfo()[2];
    }



    // Function to check if a barcode exists
    public function barcodeExists($code)
    {
        $sql = "SELECT COUNT(*) FROM barcode WHERE code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }



    // Function to reset decoded_status to 0 for all barcodes of the user
    public function resetDecodedStatus($userId)
    {
        $sql = "UPDATE barcode SET decoded_status = 0 WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }





    // Check if user has completed a decode
    public function hasCompletedDecode($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM barcode WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }





    // Function to retrieve barcodes for a user
    public function getBarcodesByUserId($userId)
    {
        $sql = "SELECT * FROM barcode WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /** --------------------------------------------Location Sharing-------------------------------------------- */

    // Check if the user has shared their location
    public function hasSharedLocation($userId)
    {
        // Prepare the SQL query to count records with share_status = 1
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM location WHERE user_id = :user_id AND share_status = 1");

        // Bind parameters and execute
        $stmt->execute([':user_id' => $userId]);

        // Return true if at least one record exists, false otherwise
        return $stmt->fetchColumn() > 0; // true if shared, false otherwise
    }

    // Function to check if the location has been shared
    public function checkShareStatus($userId)
    {
        $sql = "SELECT share_status FROM location WHERE user_id = :user_id AND share_status = 1 LIMIT 1"; // 1 indicates shared
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Check if any row was returned
        return $stmt->rowCount() > 0; // Returns true if shared, false otherwise
    }

    // Function to add a location record
    public function addLocation($params)
    {
        // Prepare the SQL statement to insert the location record
        $sql = "INSERT INTO location (user_id, lat, lon, location_url, date, share_status) 
                    VALUES (:user_id, :lat, :lon, :location_url, :date, :share_status)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':lat', $params['lat'], PDO::PARAM_STR);
        $stmt->bindParam(':lon', $params['lon'], PDO::PARAM_STR);
        $stmt->bindParam(':location_url', $params['location_url'], PDO::PARAM_STR);
        $stmt->bindParam(':date', $params['date'], PDO::PARAM_STR);
        $shareStatus = 0;  // Initial share status is false (0)
        $stmt->bindParam(':share_status', $shareStatus, PDO::PARAM_INT);

        // Execute the insert statement
        if ($stmt->execute()) {
            // Update the share status to 1 (true) after successful location insertion
            $this->updateShareStatus($params['user_id']);
            return "Location record inserted successfully.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Function to update the share status after location is shared
    public function updateShareStatus($userId)
    {
        $sql = "UPDATE location SET share_status = 1 WHERE user_id = :user_id AND share_status = 0";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute() ? "Share status updated successfully." : "Error: " . $stmt->errorInfo()[2];
    }



    // Function to retrieve locations for a user
    public function getLocationsByUserId($userId)
    {
        $sql = "SELECT * FROM location WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
