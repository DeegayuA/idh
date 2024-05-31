<?php
// Start the session
session_start();

// Include the database connection file
require_once('DBConnection.php');

// Check if the phone_number POST variable is set
if(isset($_POST['phone_number'])) {
    // Sanitize the phone number input
    $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);

    // Query the database for patient data
    $sql = "SELECT * FROM `queue_list` WHERE `phone_number` = '$phone_number' ORDER BY `date_created` DESC LIMIT 1";
    $result = $conn->query($sql);

    // Check if any data was found
    if($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Return the patient data as JSON
        echo json_encode([
            'status' => 'success',
            'data' => [
                'customer_name' => $row['customer_name'],
                'age' => $row['age'],
                'sex' => $row['sex']
            ]
        ]);
    } else {
        // No data found for the given phone number
        echo json_encode(['status' => 'not_found']);
    }
} else {
    // No phone number provided
    echo json_encode(['status' => 'error', 'message' => 'Phone number is required']);
}
?>
