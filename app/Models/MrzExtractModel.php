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
        $sql = "INSERT INTO mrz (user_id, mrz_line1, mrz_line2, mrz_line3, msg_id, file_id, decoded_status, date) 
                        VALUES (:user_id, :mrz_line1, :mrz_line2, :mrz_line3, :msg_id, :file_id, :decoded_status, :date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mrz_line1', $params['mrz_line1'], PDO::PARAM_STR);
        $stmt->bindParam(':mrz_line2', $params['mrz_line2'], PDO::PARAM_STR);
        $stmt->bindParam(':mrz_line3', $params['mrz_line3'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':decoded_status', $params['decoded_status'], PDO::PARAM_BOOL);
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
        $sql = "SELECT COUNT(*) FROM `mrz` WHERE msg_id = :msg_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':msg_id', $msgId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
