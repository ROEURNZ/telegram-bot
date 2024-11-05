<?php

class Admin

{
    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }
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
}
