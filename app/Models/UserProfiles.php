<?php

class UserProfiles
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }

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
}
