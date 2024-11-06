<?php

$pdo = require_once __DIR__ . '/../../database/connection.php';
class DecodeModel
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = EzzeTeamDatabase::getInstance();
    }

    // Function to add a barcode record
    public function addBarcode($params)
    {
        // Check if the barcode already exists
        if ($this->barcodeExists($params['code'])) {
            return "Error: Barcode already exists.";
        }

        // If the barcode doesn't exist, proceed with the insert
        $sql = "INSERT INTO `barcodes` (user_id, type, code, msg_id, file_id, file_unique_id, decoded_status) 
            VALUES (:user_id, :type, :code, :msg_id, :file_id, :file_unique_id, :decoded_status)";
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $params['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $params['type'], PDO::PARAM_STR);
        $stmt->bindParam(':code', $params['code'], PDO::PARAM_STR);
        $stmt->bindParam(':msg_id', $params['msg_id'], PDO::PARAM_INT);
        $stmt->bindParam(':file_id', $params['file_id'], PDO::PARAM_STR);
        $stmt->bindParam(':file_unique_id', $params['file_unique_id'], PDO::PARAM_STR);
        $stmt->bindParam(':decoded_status', $params['decoded_status'], PDO::PARAM_BOOL);
        // $stmt->bindParam(':decoded_status', $params['decoded_status'], PDO::PARAM_INT);


        // Execute the insert statement
        if ($stmt->execute()) {
            // Update the share status to 1 (true) after successful location insertion
            $this->updateDecodeStatus($params['user_id']);
            return "Decoded record inserted successfully.";
        } else {
            return "Error: " . $stmt->errorInfo()[2];
        }
    }

    // Function to check if a barcode exists
    public function barcodeExists($code)
    {
        $sql = "SELECT COUNT(*) FROM `barcodes` WHERE code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Function to reset decoded_status to 0 for all barcodes of the user
    public function updateDecodeStatus($userId)
    {
        $sql = "UPDATE `barcodes` SET decoded_status = 1 WHERE user_id = :user_id AND decoded_status = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Check if user has completed a decode

    public function hasCompletedDecode($userId)
    {
        $sql = "SELECT COUNT(*) FROM `barcodes` WHERE user_id = :user_id AND decoded_status = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }



    // Function to retrieve barcodes for a user
    public function getBarcodesByUserId($userId)
    {
        $sql = "SELECT * FROM `barcodes` WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
