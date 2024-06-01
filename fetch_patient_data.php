<?php
// Start the session
session_start();

// Include the database connection file
require_once('DBConnection.php');

// Check if the phone_number POST variable is set
if(isset($_POST['phone_number'])) {
    // Sanitize the phone number input
    $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);

    // Get patient history using the DBConnection method
    $patientHistory = $conn->getPatientHistory($phone_number, 1);

    // Check if any data was found
    if(!empty($patientHistory)) {
        // Get the most recent patient data
        $recentPatient = $patientHistory[0];

        // Return the patient data as JSON
        echo json_encode([
            'status' => 'success',
            'data' => [
                'customer_name' => $recentPatient['customer_name'],
                'age' => $recentPatient['age'],
                'sex' => $recentPatient['sex']
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
