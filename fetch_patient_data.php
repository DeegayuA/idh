<?php
// Start the session
session_start();

// Include the database connection file
require_once('DBConnection.php');

// Check if the phone_number, nic or unique_person_id POST variable is set
if(isset($_POST['phone_number']) || isset($_POST['nic']) || isset($_POST['unique_person_id'])) {
    $phone_number = isset($_POST['phone_number']) ? filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING) : '';
    $nic = isset($_POST['nic']) ? filter_var($_POST['nic'], FILTER_SANITIZE_STRING) : '';
    $unique_person_id = isset($_POST['unique_person_id']) ? filter_var($_POST['unique_person_id'], FILTER_SANITIZE_STRING) : '';

    // Get patient history using the DBConnection method based on phone number, NIC, or unique person ID
    $patientHistory = [];
    if(!empty($phone_number)) {
        $patientHistory = $conn->getPatientHistory($phone_number, 1);
    } elseif(!empty($nic)) {
        $patientHistory = $conn->getPatientHistory($nic, 1);
    } elseif(!empty($unique_person_id)) {
        $patientHistory = $conn->getPatientDataByUniquePersonID($unique_person_id);
    }

    // Check if any data was found
    if(!empty($patientHistory)) {
        // Get the most recent patient data
        $recentPatient = $patientHistory[0];

        // Return the patient data as JSON
        echo json_encode([
            'status' => 'success',
            'data' => $recentPatient
        ]);
        exit(); // Add this line to stop further execution
    } else {
        // No data found for the given phone number, NIC, or unique person ID
        echo json_encode(['status' => 'not_found']);
        exit(); // Add this line to stop further execution
    }
} else {
    // No phone number, NIC, or unique person ID provided
    echo json_encode(['status' => 'error', 'message' => 'Phone number, NIC, or unique person ID is required']);
    exit(); // Add this line to stop further execution
}
?>
