<?php
require_once('DBConnection.php');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['encryptedPhoneNumber'])) {
    $encryptedPhoneNumber = $input['encryptedPhoneNumber'];
    $conn = new DBConnection();

    $decryptedPhoneNumber = $conn->decrypt_data($encryptedPhoneNumber);

    if ($decryptedPhoneNumber !== false) {
        echo json_encode(['status' => 'success', 'decryptedPhoneNumber' => $decryptedPhoneNumber]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to decrypt phone number.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No phone number provided.']);
}
?>
