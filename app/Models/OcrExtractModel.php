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

        $sql = "INSERT INTO `ocr_tax_invoice` 
                (user_id, tin, lat, lon, location_url, ocrtext, msg_id, raw_data, file_id, ocrhasvat, taxincluded, date) 
                VALUES (:user_id, :tin, :lat, :lon, :location_url, :ocrtext, :msg_id, :raw_data, :file_id, :ocrhasvat, :taxincluded, :date)";
        $stmt = $this->pdo->prepare($sql);

        // Binding parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':tin', $params['tin'], PDO::PARAM_STR);
        $stmt->bindParam(':lat', $params['lat'], PDO::PARAM_STR);
        $stmt->bindParam(':lon', $params['lon'], PDO::PARAM_STR);
        $stmt->bindParam(':location_url', $params['location_url'], PDO::PARAM_STR);
        $stmt->bindParam(':ocrtext', $params['ocrtext'], PDO::PARAM_INT);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':raw_data', $params['raw_data'], PDO::PARAM_STR);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':ocrhasvat', $params['ocrhasvat'], PDO::PARAM_INT);
        $stmt->bindParam(':taxincluded', $params['taxincluded'], PDO::PARAM_INT);
        $stmt->bindParam(':date', $params['date']);

        if ($stmt->execute()) {
            return "OCR data inserted successfully.";
        } else {
            error_log("Database error: " . implode(", ", $stmt->errorInfo()));
            return "Error: " . $stmt->errorInfo()[2];
        }
    }


    public function addLocationOcr($params)
    {
        // SQL query to update `lat`, `lon`, and `location_url` in `ocr_tax_invoice` table based on `tin` and `user_id`
        $sql = "UPDATE `ocr_tax_invoice` 
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
