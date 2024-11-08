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
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id OR username = :username OR phone_number = :phone_number";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $params['user_id'],
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number']
        ]);

        return $checkStmt->fetchColumn() > 0;
    }

    function registerUser($params) 
    {
        // Check if the user already exists
        if ($this->userExists($params)) {
            return "Error: User already exists.";
        }

        // SQL statement for inserting a new user, including the previous_language column
        $sql = "INSERT INTO `user_profiles` (user_id, chat_id, msg_id, first_name, last_name, username, phone_number, created_at, date, language) 
            VALUES (:user_id, :chat_id, :msg_id, :first_name, :last_name, :username, :phone_number, NOW(), :date, :language)";

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        // Execute the statement with the parameters, defaulting previous_language to 'en'
        return $stmt->execute([
            ':user_id' => $params['user_id'],
            ':chat_id' => $params['chat_id'],
            ':msg_id' => $params['msg_id'],
            ':first_name' => $params['first_name'],
            ':last_name' => $params['last_name'],
            ':username' => $params['username'],
            ':phone_number' => $params['phone_number'],
            ':date' => $params['date'],
            ':language' => $params['language'],
        ]) ? "Record inserted successfully." : "Error: " . $stmt->errorInfo()[2];
    }


    function checkUserExists($userId)
    {
        $checkSql = "SELECT COUNT(*) FROM `user_profiles` WHERE user_id = :user_id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':user_id' => $userId]);
        return $checkStmt->fetchColumn() > 0;
    }

    function checkUniqueConstraints($username, $phoneNumber, $userId)
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

    function updateUser($params)
    {
        if (!$this->checkUserExists($params['user_id'])) {
            return "Error: User not found.";
        }

        if (!$this->checkUniqueConstraints($params['username'], $params['phone_number'], $params['user_id'])) {
            return "Error: Username or phone number already exists.";
        }

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

    public function getUserLanguage($chatId)
    {
        $sql = "SELECT language FROM `user_profiles` WHERE chat_id = :chat_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['chat_id' => $chatId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['language'] : 'en';
    }



    public function updateUserLanguage($chatId, $language)
    {
        $sql = "UPDATE `user_profiles` SET language = :language WHERE chat_id = :chat_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->bindParam(':language', $language);
        return $stmt->execute();
    }


    public function getUsername($userId)
    {
        $sql = "SELECT username FROM `user_profiles` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() ?: null;
    }


    // not used
    // public function changeCompleteUserStep($user_id, $val)
    // {
    //     $sql = "UPDATE `user_profiles` SET is_step_complete = ? WHERE user_id = ?";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->bindParam('user_id', $user_id);
    //     $stmt->bindParam('val', $val);
    //     return $stmt->execute();
    // }

    /**
     *      `step` varchar(50) DEFAULT NULL,
            `is_step_complete` tinyint (1) NOT NULL DEFAULT 1,
     */
}
