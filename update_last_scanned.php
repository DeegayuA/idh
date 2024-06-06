<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once('DBConnection.php');

$conn = new DBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $encrypted_unique_person_id = isset($_POST['encrypted_unique_person_id']) ? $_POST['encrypted_unique_person_id'] : '';

    if ($encrypted_unique_person_id) {
        $updateStatus = $conn->updateLastScannedDatetime($encrypted_unique_person_id);

        if ($updateStatus) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'Failed to update last scanned datetime.']);
        }
    } else {
        echo json_encode(['status' => 'failed', 'message' => 'Invalid request.']);
    }
}
?>
