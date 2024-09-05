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

// Fetch active cashier (doctor) data
$active_cashiers_response = $conn->get_active_cashiers();
$active_cashiers = json_decode($active_cashiers_response, true)['data'];
$active_cashier_count = count($active_cashiers);

// Define an array to hold doctor colors
$doctor_colors = array();

// Extract colors from the home.php file
$index_php_file = file_get_contents('home.php'); 
preg_match_all('/--doctor-\d+-color: (.*?);/', $index_php_file, $matches);
if ($matches) {
    $doctor_colors = $matches[1];
}

// Calculate the number of doctor rooms to display (max 10)
$doctor_room_count = min(10, $active_cashier_count);

// Prepare the JSON response
$response = array("data" => array());
for ($i = 0; $i < $doctor_room_count; $i++) {
    $doctor_name = isset($active_cashiers[$i]['name']) ? $active_cashiers[$i]['name'] : "Doctor " . ($i + 1);
    
    $response["data"][] = array(
        "name" => $doctor_name,
        "color" => isset($doctor_colors[$i]) ? $doctor_colors[$i] : null // Check if color is set, otherwise set to null
    );
}

// Get the public IP address of the server
$server_ip = file_get_contents('https://api.ipify.org');

// Send the JSON response
echo json_encode($response);

// Output the server IP address
echo "\nServer IP Address: " . $server_ip;
echo " add this IP to ESP32";
?>
