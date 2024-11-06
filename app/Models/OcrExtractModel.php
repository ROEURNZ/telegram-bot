<?php


class OcrExtractModel
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }
    public function addOcrData($params)
    {

        $sql = "INSERT INTO ocr (user_id, msg_id, raw_data, file_id, status, date, ocrtext, ocrhasvat, taxincluded) 
                VALUES (:user_id, :msg_id, :raw_data, :file_id, :status, :date, :ocrtext, :ocrhasvat, :taxincluded)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':raw_data', $params['rawData'], PDO::PARAM_STR);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $params['status'], PDO::PARAM_INT);
        $stmt->bindParam(':date', $params['date']);

        // Bind the extracted OCR fields
        $stmt->bindParam(':ocrtext', $params['taxIdentifiers'], PDO::PARAM_INT);
        $stmt->bindParam(':ocrhasvat', $params['ocrhasvat'], PDO::PARAM_INT);
        $stmt->bindParam(':taxincluded', $params['taxincluded'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "OCR data inserted successfully.";
        } else {
            error_log("Database error: " . implode(", ", $stmt->errorInfo())); // Log the error
            return "Error: " . $stmt->errorInfo()[2];
        }
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
