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
        // Check if user exists
        if ($this->checkUserExists($params['user_id'])) {
            return "User already exists.";
        }

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

    public function checkUserExists($userId)
    {
        return $this->isUserIdExists($userId);
    }

    private function isUserIdExists($userId)
    {
        // Check if the user already exists in the database
        $sql = "SELECT COUNT(*) FROM users WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() > 0; // Returns true if user exists
    }



    // Function to add a barcode record
    public function addBarcode($params)
    {
        // Check if the barcode already exists
        if ($this->barcodeExists($params['code'])) {
            return "Error: Barcode already exists.";
        }

        // If the barcode doesn't exist, proceed with the insert
        $sql = "INSERT INTO barcode (user_id, type, code, msg_id, file_id, file_unique_id) VALUES (:user_id, :type, :code, :msg_id, :file_id, :file_unique_id)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $params['type'], PDO::PARAM_STR);
        $stmt->bindParam(':code', $params['code'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);              // Replaced image_id with file_id
        $stmt->bindParam(':file_unique_id', $params['file_unique_id'], PDO::PARAM_STR); // Added file_unique_id

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


    // Function to retrieve barcodes for a user
    public function getBarcodesByUserId($userId)
    {
        $sql = "SELECT * FROM barcode WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Function to add a location record
    public function addLocation($params)
    {
        $sql = "INSERT INTO location (user_id, lat, lon, location_url, date) VALUES (:user_id, :lat, :lon, :location_url, :date)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':lat', $params['lat'], PDO::PARAM_STR);
        $stmt->bindParam(':lon', $params['lon'], PDO::PARAM_STR);
        $stmt->bindParam(':location_url', $params['location_url'], PDO::PARAM_STR);
        $stmt->bindParam(':date', $params['date'], PDO::PARAM_STR); // Binding the date parameter

        return $stmt->execute() ? "Location record inserted successfully." : "Error: " . $stmt->errorInfo()[2];
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




    function updateTgUsername($userId, $tgUsername)
    {
        if (empty($userId)) {
            return false;
        }

        $sql = "UPDATE user_profiles SET tg_username = :tg_username WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':tg_username' => $tgUsername, ':user_id' => $userId]);
    }

    function incorrectReplyUserStatus($logID, $userStatus)
    {
        if (is_null($logID)) {
            return false;
        }

        $param = json_encode($userStatus, JSON_UNESCAPED_UNICODE);
        $sql = "UPDATE request_reply_log SET wrong_reply_user_stat = :user_status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_status' => $param, ':id' => $logID]);
    }



    // Check if the user exists by chatId
    public function isUserChatIdExists($chatId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE chat_id = :chat_id");
        $stmt->execute([':chat_id' => $chatId]);
        return $stmt->fetchColumn() > 0;
    }


    // Store decoded barcode for the user
    public function storeDecodedBarcode($chatId, $barcode)
    {
        $stmt = $this->pdo->prepare("INSERT INTO barcodes (chat_id, barcode) VALUES (:chat_id, :barcode)");
        $stmt->execute([
            ':chat_id' => $chatId,
            ':barcode' => $barcode
        ]);
    }

    // Check if user has completed a decode
    public function hasCompletedDecode($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM barcodes WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }

    // Store user location
    public function storeLocation($chatId, $latitude, $longitude)
    {
        $stmt = $this->pdo->prepare("INSERT INTO locations (chat_id, latitude, longitude) VALUES (:chat_id, :latitude, :longitude)");
        $stmt->execute([
            ':chat_id' => $chatId,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
    }

    // Check if the user has shared location
    public function hasSharedLocation($chatId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM locations WHERE chat_id = :chat_id");
        $stmt->execute([':chat_id' => $chatId]);
        return $stmt->fetchColumn() > 0;
    }

    // Add a profile to the Profiles table
    public function insertProfile($userId, $tgUsername, $name, $surname, $phone, $lang, $msgId)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO Profiles (userId, tgUsername, Name, Surname, Phone, Lang, MsgId) 
            VALUES (:userId, :tgUsername, :name, :surname, :phone, :lang, :msgId)
        ");
        $stmt->execute([
            ':userId' => $userId,
            ':tgUsername' => $tgUsername,
            ':name' => $name,
            ':surname' => $surname,
            ':phone' => $phone,
            ':lang' => $lang,
            ':msgId' => $msgId
        ]);

        // Return the last inserted ID
        return $this->pdo->lastInsertId();
    }
}
