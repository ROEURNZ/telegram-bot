<?php

class MrzExtractModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }
    public function addMRZData($params)
    {
        // Check if the MRZ already exists
        if ($this->mrzExists($params['msg_id'])) {
            return "Error: Mrz already exists.";
        }
        $sql = "INSERT INTO `mrz_uic` (user_id, file_id, msg_id,mrz_raw, uic_data, lat, lon, location_url, mrz_status, date) 
                        VALUES (:user_id, :file_id, :msg_id, :mrz_raw, :uic_data, :lat, :lon, :location_url,  :mrz_status, :date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mrz_raw', $params['mrz_raw'], PDO::PARAM_STR);
        $stmt->bindParam(':uic_data', $params['uic_data']);
        $stmt->bindParam(':lat', $params['lat'], PDO::PARAM_STR);
        $stmt->bindParam(':lon', $params['lon'], PDO::PARAM_STR);
        $stmt->bindParam(':location_url', $params['location_url'], PDO::PARAM_STR);
        $stmt->bindParam(':mrz_status', $params['mrz_status'], PDO::PARAM_BOOL);
        $stmt->bindParam(':date', $params['date']);

        if ($stmt->execute()) {
            return "MRZ data inserted successfully.";
        } else {
            error_log("Database error: " . implode(", ", $stmt->errorInfo()));
            return "Error: " . $stmt->errorInfo()[2];
        }
    }
    // Function to check if a MRZ exists
    public function mrzExists($msgId)
    {
        $sql = "SELECT COUNT(*) FROM `mrz_uic` WHERE msg_id = :msg_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':msg_id', $msgId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }


    public function addLocationMrz($params)
    {
        // SQL query to update `lat`, `lon`, and `location_url` in `mrz_uic` table based on `user_id`
        $sql = "UPDATE `mrz_uic` 
            SET lat = :lat, lon = :lon, location_url = :location_url 
            WHERE user_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':lat', $params['lat'], PDO::PARAM_STR);
        $stmt->bindParam(':lon', $params['lon'], PDO::PARAM_STR);
        $stmt->bindParam(':location_url', $params['location_url'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "Location data updated successfully.";
        } else {
            error_log("Database error: " . implode(", ", $stmt->errorInfo()));
            return "Error: " . $stmt->errorInfo()[2];
        }
    }
}
