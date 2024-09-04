<?php
header('Content-Type: application/json');

require_once('./../DBConnection.php');
$conn = new DBConnection();

// Fetch total patients
$totalPatientsQuery = "SELECT COUNT(*) as total FROM queue_list";
$totalPatientsResult = $conn->query($totalPatientsQuery);
if ($totalPatientsResult) {
    $totalPatientsRow = $totalPatientsResult->fetchArray(SQLITE3_ASSOC);
    $totalPatients = $totalPatientsRow ? $totalPatientsRow['total'] : 0;
} else {
    $totalPatients = 0;
}

// Fetch total active doctors
$totalActiveDoctorsQuery = "SELECT COUNT(*) as total FROM cashier_list WHERE log_status = 0";
$totalActiveDoctorsResult = $conn->query($totalActiveDoctorsQuery);
if ($totalActiveDoctorsResult) {
    $totalActiveDoctorsRow = $totalActiveDoctorsResult->fetchArray(SQLITE3_ASSOC);
    $totalDoctors = $totalActiveDoctorsRow ? $totalActiveDoctorsRow['total'] : 0;
} else {
    $totalDoctors = 0;
}

// Fetch total patients in the queue
$totalPatientsInQueueQuery = "SELECT COUNT(*) as total 
                              FROM queue_list 
                              WHERE status = 0 
                              AND DATE(date_created) = DATE('now')";
$totalPatientsInQueueResult = $conn->query($totalPatientsInQueueQuery);
if ($totalPatientsInQueueResult) {
    $totalPatientsInQueueRow = $totalPatientsInQueueResult->fetchArray(SQLITE3_ASSOC);
    $totalPatientsInQueue = $totalPatientsInQueueRow ? $totalPatientsInQueueRow['total'] : 0;
} else {
    $totalPatientsInQueue = 0;
}

// Fetch first patient entry date and calculate days since system started
$firstPatientDateQuery = "SELECT MIN(date(date_created)) as firstDate FROM queue_list";
$firstPatientDateResult = $conn->query($firstPatientDateQuery);
if ($firstPatientDateResult) {
    $firstPatientDateRow = $firstPatientDateResult->fetchArray(SQLITE3_ASSOC);
    $firstPatientDate = $firstPatientDateRow ? $firstPatientDateRow['firstDate'] : 'Unknown';
    $daysSinceStart = (new DateTime())->diff(new DateTime($firstPatientDate))->days;
} else {
    $firstPatientDate = 'Unknown';
    $daysSinceStart = 0;
}

// Define the new news date
$newNewsDate = date('Y-m-d'); // Get the current date in 'YYYY-MM-DD' format

// Update local news
$localNewsEnglish = [
    "This is a pilot project between the National Institute of Infectious Diseases and the University of Kelaniya, started on {$newNewsDate}.",
    "Did you know? National Institute of Infectious Diseases has treated over {$totalPatients} patients successfully using this system. Currently, there are {$totalPatientsInQueue} patients in the queue."
];

$localNewsSinhala = [
    "මෙය ජාතික බෝවන රෝග විද්‍යායතනය සහ කැලණිය විශ්වවිද්‍යාලය මගින්, {$newNewsDate} දින ආරම්භ කරන ලද නියමු ව්‍යාපෘතියකි.",
    "ඔබ දන්නවාද? ජාතික බෝවන රෝග විද්‍යායතනය, ඒදින සිට {$totalPatients} කට වැඩි රෝගීන් සංඛ්‍යාවකගේ කාලය ඉතිරි කිරිමට මෙම ව්‍යාපෘතිය සමත් වී ඇත. මේ වන විට පෝලිම තුළ රෝගීන් සංඛ්‍යාව: {$totalPatientsInQueue}."
];

$response = [
    "totalPatients" => $totalPatients,
    "totalDoctors" => $totalDoctors,
    "totalPatientsInQueue" => $totalPatientsInQueue,
    "daysSinceStart" => $daysSinceStart,
    "localNews" => [
        "English" => implode(" &nbsp; | &nbsp; ", $localNewsEnglish),
        "Sinhala" => implode(" &nbsp; | &nbsp; ", $localNewsSinhala)
    ]
];

echo json_encode($response);
