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
$totalPatientsInQueueQuery = "SELECT COUNT(*) as total FROM queue_list WHERE status = 0"; 
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

// Update local news
$localNews = [
    "English" => "Did you know? National Institute of Infectious Diseases has treated over {$totalPatients} patients successfully since {$firstPatientDate}. Currently, there are {$totalPatientsInQueue} patients in the queue.",
    "Sinhala" => "ඔබ දන්නවාද? ජාතික බෝවන රෝග විද්‍යායතනය, {$totalPatients} කට වැඩි රෝගීන් සාර්ථකව ප්‍රතිකාර කර ඇත. පළමු රෝගියා ඇතුළත් වූ දිනය: {$firstPatientDate}. මේ වන විට පෝලිම තුළ රෝගීන් සංඛ්‍යාව: {$totalPatientsInQueue}."
];

$response = [
    "totalPatients" => $totalPatients,
    "totalDoctors" => $totalDoctors,
    "totalPatientsInQueue" => $totalPatientsInQueue,
    "daysSinceStart" => $daysSinceStart,
    "localNews" => $localNews
];

echo json_encode($response);
?>
