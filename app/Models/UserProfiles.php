<?php

class UserProfiles
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }

    function userExists($params)
    {
        // Check if username or phone_number exist in $params, otherwise set them to empty string
        $username = isset($params['username']) ? $params['username'] : '';
        $phoneNumber = isset($params['phone_number']) ? $params['phone_number'] : '';
    
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id OR username = :username OR phone_number = :phone_number";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $params['user_id'],
            ':username' => $username,
            ':phone_number' => $phoneNumber
        ]);
    
        return $checkStmt->fetchColumn() > 0;
    }
    



    public function registerUser($params)
    {
        // Check if the user already exists
        if ($this->userExists($params)) {
            return "Error: User already exists.";
        }

        // Default permission to 1 if not set
        $permission = isset($params['permission']) ? $params['permission'] : 1;

        // Default language to 'en' if not set
        $language = isset($params['language']) ? $params['language'] : 'en';

        // SQL statement for inserting a new user
        $sql = "INSERT INTO `user_profiles` (user_id, chat_id, msg_id, first_name, last_name, username, phone_number, permission, created_at, date, language) 
                VALUES (:user_id, :chat_id, :msg_id, :first_name, :last_name, :username, :phone_number, :permission, NOW(), :date, :language)";

        $stmt = $this->pdo->prepare($sql);

        // Execute the statement with parameters
        $executeSuccess = $stmt->execute([
            ':user_id' => $params['user_id'],
            ':chat_id' => $params['chat_id'],
            ':msg_id' => $params['msg_id'],
            ':first_name' => $params['first_name'],
            ':last_name' => $params['last_name'],
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number'],
            ':permission' => $permission,
            ':date' => $params['date'],
            ':language' => $language,
        ]);

        return $executeSuccess ? true : "Error: " . $stmt->errorInfo()[2];
    }

    // Check if user has permission (1 means allowed)
    public function hasPermission($user_id)
    {
        // Check if user exists
        $sql = "SELECT permission FROM `user_profiles` WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        // Return true if the permission is '1', otherwise false
        return $result !== false && $result == 1;
    }


    function checkUserPhoneNumberExists($userId, $phone)
    {
        // SQL query to check if the phone number exists for the specified user
        $sql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id AND phone_number = :phone_number";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':phone_number' => $phone
        ]);
    
        // If the count is greater than 0, the phone number exists for the user
        return $stmt->fetchColumn() > 0;
    }
    


    function checkUserExists($userId)
    {
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':user_id' => $userId]);
        return $checkStmt->fetchColumn() > 0;
    }



    // Helper function to check if username or phone number is unique (avoids duplicates)
    private function checkUniqueConstraints($username, $phoneNumber, $userId)
    {
        $uniqueCheckSql = "SELECT COUNT(*) FROM `user_profiles` 
                               WHERE (username = :username OR phone_number = :phone_number) 
                               AND user_id != :user_id";
        $uniqueCheckStmt = $this->pdo->prepare($uniqueCheckSql);
        $uniqueCheckStmt->execute([
            ':username' => $username,
            ':phone_number' => $phoneNumber,
            ':user_id' => $userId
        ]);
        return $uniqueCheckStmt->fetchColumn() == 0;
    }

    // Updates an existing user's details
    function updateUser($params)
    {
        if (!$this->userExists(['user_id' => $params['user_id']])) {
            return "Error: User not found.";
        }

        if (!$this->checkUniqueConstraints($params['username'], $params['phone_number'], $params['user_id'])) {
            return "Error: Username or phone number already exists.";
        }

        $updateSql = "UPDATE `user_profiles` SET chat_id = :chat_id, msg_id = :msg_id, first_name = :first_name, 
                      last_name = :last_name, username = :username, phone_number = :phone_number, date = :date, 
                      language = :language WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($updateSql);

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


    public function selectedLanguage($userId)
    {
        $sql = "SELECT language FROM `user_profiles` WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $language = $stmt->fetchColumn();
        return !empty($language);
    }



    // Retrieves user language based on user_id or returns 'en' as default if not set
    public function getUserLanguage($userId)
    {
        $sql = "SELECT language FROM `user_profiles` WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() ?: 'en';
    }


    // Updates user language preference
    public function updateUserLanguage($userId, $language)
    {
        $sql = "UPDATE `user_profiles` SET language = :language WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':language' => $language, ':user_id' => $userId]) ? "Language updated successfully." : "Error: " . $stmt->errorInfo()[2];
    }

    public function getUsername($userId)
    {
        $sql = "SELECT username FROM `user_profiles` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() ?: null;
    }
}
