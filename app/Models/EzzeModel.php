<?php
/* @ROEURNZ=> File name & directory
 * backend/app/Models/EzzeModel.php
 * 
 */

// Include db.php to get the PDO instance
$pdo = require_once __DIR__ . '/../../database/connection.php';

class EzzeModels
{
    private $pdo;

    // Use the singleton instance from db.php
    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /** ----------------------------------------Admin User Controls ----------------------------------------------- */

    // Admin function to delete a user by user ID
    public function deleteUserById($userId)
    {
        $sql = "DELETE FROM `user_profiles` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute() ? "User deleted successfully." : "Error: " . $stmt->errorInfo()[2];
    }

    // Admin function to update a user's information
    public function updateUserInfo($userId, $params)
    {
        $sql = "UPDATE `user_profiles` SET first_name = :first_name, last_name = :last_name, username = :username, 
                phone_number = :phone_number, language = :language WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':first_name' => $params['first_name'],
            ':last_name' => $params['last_name'],
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number'],
            ':language' => $params['language'],
            ':user_id' => $userId
        ]) ? "User information updated successfully." : "Error: " . $stmt->errorInfo()[2];
    }

    // Admin function to get all users
    public function getAllUsers()
    {
        $sql = "SELECT * FROM `user_profiles`";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Admin function to block or unblock a user
    public function updateUserStatus($userId, $status)
    {
        $sql = "UPDATE `user_profiles` SET is_blocked = :status WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':status' => $status, ':user_id' => $userId])
            ? "User status updated successfully."
            : "Error: " . $stmt->errorInfo()[2];
    }

    /** ----------------------------------------Admin Barcode Controls ----------------------------------------------- */

    // Admin function to delete a barcode by its code
    public function deleteBarcodeByCode($code)
    {
        $sql = "DELETE FROM `barcodes` WHERE code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        return $stmt->execute() ? "Barcode deleted successfully." : "Error: " . $stmt->errorInfo()[2];
    }

    // Admin function to retrieve all barcodes for a user
    public function getAllBarcodes()
    {
        $sql = "SELECT * FROM `barcodes`";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Admin function to update decoded status for a specific barcode
    public function updateBarcodeDecodedStatus($barcodeId, $status)
    {
        $sql = "UPDATE `barcodes` SET decoded_status = :status WHERE id = :barcode_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':status' => $status, ':barcode_id' => $barcodeId])
            ? "Barcode status updated successfully."
            : "Error: " . $stmt->errorInfo()[2];
    }

    /** ----------------------------------------Admin Location Controls ----------------------------------------------- */

    // Admin function to delete a location by user ID
    public function deleteLocationByUserId($userId)
    {
        $sql = "DELETE FROM location WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute() ? "Location deleted successfully." : "Error: " . $stmt->errorInfo()[2];
    }

    // Admin function to retrieve all locations
    public function getAllLocations()
    {
        $sql = "SELECT * FROM `location`";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Admin function to force a user's location share status
    public function forceUpdateShareStatus($userId, $status)
    {
        $sql = "UPDATE location SET share_status = :status WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':status' => $status, ':user_id' => $userId])
            ? "Share status updated successfully."
            : "Error: " . $stmt->errorInfo()[2];
    }



    // Method to retrieve all user IDs
    public function getAllUsersId()
    {
        $stmt = $this->pdo->prepare("SELECT user_id FROM `user_profiles`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function deleteBarcodes($userId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM barcode WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function deleteLocations($userId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM location WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function deleteUser($userId)
    {
        // Start a transaction
        $this->pdo->beginTransaction();

        try {
            // Delete barcodes associated with the user
            if (!$this->deleteBarcodes($userId)) {
                throw new Exception("Failed to delete barcodes.");
            }

            // Delete locations associated with the user
            if (!$this->deleteLocations($userId)) {
                throw new Exception("Failed to delete locations.");
            }

            // Delete the user
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Commit the transaction
            $this->pdo->commit();
            echo "User and related data deleted successfully.";
        } catch (Exception $e) {
            // Rollback the transaction if anything fails
            $this->pdo->rollBack();
            echo "Failed to delete user: " . $e->getMessage();
        }
    }



    /**-----------------------------------------------Register the USERS --------------------------------------------------- */

    function registerUsers($params)
    {
        // Check if the user already exists based on unique fields (user_id, username, or phone_number)
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id OR username = :username OR phone_number = :phone_number";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $params['user_id'],
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number']
        ]);

        // If a record already exists, return an error message
        if ($checkStmt->fetchColumn() > 0) {
            return "Error: User already exists.";
        }

        // SQL statement for inserting a new user
        $sql = "INSERT INTO `user_profiles` (user_id, chat_id, msg_id, first_name, last_name, username, phone_number, created_at, date, language) 
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


    function updateUser($params)
    {
        // Check if the user exists by user_id
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':user_id' => $params['user_id']]);

        // If the user does not exist, return an error message
        if ($checkStmt->fetchColumn() == 0) {
            return "Error: User not found.";
        }

        // Check for unique constraints on username and phone_number (ignore current user's values)
        $uniqueCheckSql = "SELECT COUNT(*) FROM `user_profiles` 
                       WHERE (username = :username OR phone_number = :phone_number) 
                       AND user_id != :user_id";
        $uniqueCheckStmt = $this->pdo->prepare($uniqueCheckSql);
        $uniqueCheckStmt->execute([
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number'],
            ':user_id' => $params['user_id']
        ]);

        // If another user with the same username or phone number exists, return an error
        if ($uniqueCheckStmt->fetchColumn() > 0) {
            return "Error: Username or phone number already exists.";
        }

        // SQL statement for updating an existing user
        $updateSql = "UPDATE `user_profiles` 
                  SET chat_id = :chat_id, 
                      msg_id = :msg_id, 
                      first_name = :first_name, 
                      last_name = :last_name, 
                      username = :username, 
                      phone_number = :phone_number, 
                      date = :date, 
                      language = :language 
                  WHERE user_id = :user_id";

        // Prepare the statement
        $stmt = $this->pdo->prepare($updateSql);

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

        ]) ? "User updated successfully." : "Error: " . $stmt->errorInfo()[2];
    }


    // Function to check if the user has selected a language
    public function hasSelectedLanguage($userId)
    {
        // Prepare the SQL query to check if the user has a language set
        $sql = "SELECT language FROM `user_profiles` WHERE user_id = :user_id LIMIT 1";
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


    public function getUserLanguage($chatId)
    {
        // Prepare a statement to get the user's language from the database
        $stmt = $this->pdo->prepare("SELECT language FROM `user_profiles` WHERE chat_id = :chat_id ");
        $stmt->execute(['chat_id' => $chatId]);

        // Fetch the language preference
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the language, or a default value if not found
        return $result ? $result['language'] : 'en'; // default to 'en' if not found
    }


    // Method to update user's language
    public function updateUserLanguage($chatId, $language)
    {
        $stmt = $this->pdo->prepare("UPDATE `user_profiles` SET language = :language WHERE chat_id = :chat_id");
        return $stmt->execute(['language' => $language, 'chat_id' => $chatId]);
    }


    public function checkUserExists($userId)
    {
        return $this->isUserIdExists($userId);
    }

    private function isUserIdExists($userId)
    {
        // Check if the user already exists in the database
        $sql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }


    public function tgUsername($userId)
    {
        // Prepare the SQL statement to fetch the username by user ID
        $sql = "SELECT username FROM `user_profiles` WHERE user_id = :user_id";
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

        $sql = "UPDATE `user_profiles` SET username = :username WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':username' => $username, ':user_id' => $userId]);
    }


    // Check if the user exists by chatId
    public function isUserChatIdExists($chatId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `user_profiles` WHERE chat_id = :chat_id");
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
        $sql = "INSERT INTO `barcodes` (user_id, type, code, msg_id, file_id, file_unique_id, decoded_status) 
            VALUES (:user_id, :type, :code, :msg_id, :file_id, :file_unique_id, :decoded_status)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $params['type'], PDO::PARAM_STR);
        $stmt->bindParam(':code', $params['code'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':file_unique_id', $params['file_unique_id'], PDO::PARAM_STR);
        $stmt->bindParam(':decoded_status', $params['decoded_status'], PDO::PARAM_BOOL);
        // $stmt->bindParam(':decoded_status', $params['decoded_status'], PDO::PARAM_INT);


        // Execute the insert statement
        if ($stmt->execute()) {
            // Update the share status to 1 (true) after successful location insertion
            $this->updateDecodeStatus($params['user_id']);
            return "Decoded record inserted successfully.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Function to check if a barcode exists
    public function barcodeExists($code)
    {
        $sql = "SELECT COUNT(*) FROM `barcodes` WHERE code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Function to reset decoded_status to 0 for all barcodes of the user
    public function updateDecodeStatus($userId)
    {
        $sql = "UPDATE `barcodes` SET decoded_status = 1 WHERE user_id = :user_id AND decoded_status = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Check if user has completed a decode

    public function hasCompletedDecode($userId)
    {
        $sql = "SELECT COUNT(*) FROM `barcodes` WHERE user_id = :user_id AND decoded_status = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }



    // Function to retrieve barcodes for a user
    public function getBarcodesByUserId($userId)
    {
        $sql = "SELECT * FROM `barcodes` WHERE user_id = :user_id";
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
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `location` WHERE user_id = :user_id AND share_status = 1");

        // Bind parameters and execute
        $stmt->execute([':user_id' => $userId]);

        // Return true if at least one record exists, false otherwise
        return $stmt->fetchColumn() > 0; // true if shared, false otherwise
    }

    // Function to check if the location has been shared
    public function checkShareStatus($userId)
    {
        $sql = "SELECT share_status FROM `location` WHERE user_id = :user_id AND share_status = 1 LIMIT 1"; // 1 indicates shared
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
        $sql = "INSERT INTO `location` (user_id, lat, lon, location_url, date, share_status) 
                    VALUES (:user_id, :lat, :lon, :location_url, :date, :share_status)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':lat', $params['lat'], PDO::PARAM_STR);
        $stmt->bindParam(':lon', $params['lon'], PDO::PARAM_STR);
        $stmt->bindParam(':location_url', $params['location_url'], PDO::PARAM_STR);
        $stmt->bindParam(':date', $params['date'], PDO::PARAM_STR);
        // $shareStatus = 0;  // Initial share status is false (0)
        // $stmt->bindParam(':share_status', $shareStatus, PDO::PARAM_INT);
        $stmt->bindParam(':share_status', $params['share_status'], PDO::PARAM_BOOL);

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
        $sql = "UPDATE `location` SET share_status = 1 WHERE user_id = :user_id AND share_status = 0";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute() ? "Share status updated successfully." : "Error: " . $stmt->errorInfo()[2];
    }



    // Function to retrieve locations for a user
    public function getLocationsByUserId($userId)
    {
        $sql = "SELECT * FROM `location` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function addOcrData($params)
    {

        // Check if the barcode already exists
        if ($this->ocrExists($params['vat_tin'])) {
            return "Error: Ocr already exists.";
        }
        $sql = "INSERT INTO ocr (user_id, vat_tin, msg_id, raw_data, file_id, status, date) 
                VALUES (:user_id, :vat_tin, :msg_id, :raw_data, :file_id, :status, :date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':vat_tin', $params['vat_tin'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id']);
        $stmt->bindParam(':raw_data', $params['text']);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $params['status']);
        $stmt->bindParam(':date', $params['date']);

        if ($stmt->execute()) {
            return "OCR data inserted successfully.";
        } else {
            error_log("Database error: " . implode(", ", $stmt->errorInfo())); // Log the error
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Function to check if a barcode exists
    public function ocrExists($vatTin)
    {
        $sql = "SELECT COUNT(*) FROM `ocr` WHERE vat_tin = :vat_tin";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':vat_tin', $vatTin, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }



    public function updateOcrLocation($params)
    {
        $sql = "UPDATE ocr SET lat = :lat, lon = :lon, location_url = :location_url, status = :status 
            WHERE user_id = :user_id AND status = 0";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':lat', $params['lat']);
        $stmt->bindParam(':lon', $params['lon']);
        $stmt->bindParam(':location_url', $params['location_url']);
        $stmt->bindParam(':status', $params['status']);
        $stmt->bindParam(':user_id', $params['user_id']);

        if ($stmt->execute()) {
            return "OCR location successfully updated";
        } else {
            return "Error updating OCR location";
        }
    }
}
