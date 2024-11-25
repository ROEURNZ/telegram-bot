<?php

class UserProfiles
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }



    // Check if user exists by user_id or username
    function userExists($params)
    {
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id OR username = :username";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $params['user_id'],
            ':username' => $params['username']
        ]);

        return $checkStmt->fetchColumn() > 0;
    }

    // Check if user already has a phone number
    function checkUserPhone($userId)
    {
        $sql = "SELECT phone_number FROM `user_profiles` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $phone_number = $stmt->fetchColumn();

        return $phone_number ? $phone_number : false;
    }

    // Pre-store basic user data (without phone number)
    public function preStore($params)
    {
        // Check if the user already exists
        if ($this->userExists($params)) {
            return "Error: User already exists.";
        }

        // SQL statement for inserting basic user data (without phone number)
        $sql = "INSERT INTO `user_profiles` (user_id, chat_id, msg_id, first_name, last_name, username, created_at, date, language) 
                VALUES (:user_id, :chat_id, :msg_id, :first_name, :last_name, :username, NOW(), :date, :language)";

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        // Execute the statement with the parameters
        return $stmt->execute([
            ':user_id' => $params['user_id'],
            ':chat_id' => $params['chat_id'],
            ':msg_id' => $params['msg_id'],
            ':first_name' => $params['first_name'],
            ':last_name' => $params['last_name'],
            ':username' => $params['username'],
            ':date' => $params['date'],
            ':language' => $params['language'],
        ]) ? "Pre-store successful." : "Error: " . $stmt->errorInfo()[2];
    }


    // Register user and add phone number
    function registerUser($params)
    {
        // Check if the user already exists by user_id
        if ($this->userExists(['user_id' => $params['user_id'], 'username' => $params['username']])) {
            return "Error: User with this ID or username already exists.";
        }

        // Check if the user already has a phone number
        $existingPhoneNumber = $this->checkUserPhone($params['user_id']);
        if ($existingPhoneNumber) {
            return "Error: User already has a phone number ($existingPhoneNumber).";
        }

        // SQL statement for inserting phone number and other details
        $sql = "UPDATE `user_profiles` 
                    SET phone_number = :phone_number, 
                        updated_at = NOW()
                    WHERE user_id = :user_id";

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        // Execute the statement with the parameters
        return $stmt->execute([
            ':user_id' => $params['user_id'],
            ':phone_number' => $params['phone_number'],
        ]) ? "User registered successfully with phone number." : "Error: " . $stmt->errorInfo()[2];
    }

    public function checkUserExists($params)
    {
        // SQL query to check if the user exists based on user_id or username
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id OR username = :username";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $params['user_id'],
            ':username' => $params['username']
        ]);

        // Return true if a user is found, false otherwise
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
