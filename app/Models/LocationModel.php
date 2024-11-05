<?php

class LocationModel
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }

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
}
