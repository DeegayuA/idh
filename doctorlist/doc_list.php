<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('./../DBConnection.php');
$conn = new DBConnection();

// Fetch active cashier (doctor) IDs
$active_cashiers_response = $conn->get_active_cashiers();
$active_cashiers = json_decode($active_cashiers_response, true)['data'];
$active_cashier_count = count($active_cashiers);

// Define an array to hold doctor colors
$doctor_colors = array();

// Extract colors from the index.php file
$index_php_file = file_get_contents('home.php'); 
// $index_php_file = file_get_contents(__FILE__); 
preg_match_all('/--doctor-\d+-color: (.*?);/', $index_php_file, $matches);
if ($matches) {
    $doctor_colors = $matches[1];
}

// Calculate the number of doctor rooms to display
$doctor_room_count = min(10, $active_cashier_count); // Limit to maximum 10 rooms

// Prepare the JSON response
$response = array("data" => array());
for ($i = 0; $i < $doctor_room_count; $i++) {
    $response["data"][] = array(
        "name" => "Doctor Room " . ($i + 1),
        "color" => isset($doctor_colors[$i]) ? $doctor_colors[$i] : null // Check if color is set, otherwise set it to null
    );
}

// Get the public IP address of the server
$server_ip = file_get_contents('https://api.ipify.org');

// Send the JSON response
echo json_encode($response);

// Output the server IP address
echo "\n\n\n Server IP Address: " . $server_ip;
echo "\n add this IP to ESP32";
?>
