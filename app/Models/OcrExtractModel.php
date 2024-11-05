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
        // Check if the barcode already exists
        if ($this->ocrExists($params['vat_tin'])) {
            return "Error: Ocr already exists.";
        }

        $sql = "INSERT INTO ocr (user_id, vat_tin, msg_id, raw_data, file_id, status, date) 
                    VALUES (:user_id, :vat_tin, :msg_id, :raw_data, :file_id, :status, :date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':vat_tin', $params['vat_tin'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id']);
        $stmt->bindParam(':raw_data', $params['text']);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $params['status']);
        $stmt->bindParam(':date', $params['date']);

        if ($stmt->execute()) {
            return "OCR data inserted successfully.";
        } else {
            error_log("Database error: " . implode(", ", $stmt->errorInfo())); // Log the error
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Function to check if a barcode exists
    public function ocrExists($vatTin)
    {
        $sql = "SELECT COUNT(*) FROM `ocr` WHERE vat_tin = :vat_tin";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':vat_tin', $vatTin, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
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
