<?php
// Start the session
session_start();

// Include the database connection file
require_once('DBConnection.php');

// Function to output debug information
function debug($message) {
    echo "<pre>";
    print_r($message);
    echo "</pre>";
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the unique_person_id POST variable is set
    if(isset($_POST['unique_person_id'])) {
        // Sanitize the input
        $unique_person_id = isset($_POST['unique_person_id']) ? filter_var($_POST['unique_person_id'], FILTER_SANITIZE_STRING) : '';

        // Debugging information
        debug("Unique Person ID: " . $unique_person_id);

        // Get patient data using the DBConnection method based on unique person ID
        $patientData = $conn->getPatientDataByUniquePersonID($unique_person_id);

        // Check if any errors occurred during the execution of the function
        if ($patientData === false) {
            // Output the error message
            echo "Error retrieving patient data.";
        } else {
            // Output the result for debugging
            debug("Patient Data: ");
            debug($patientData);
        }
    } else {
        // No unique person ID provided
        echo json_encode(['status' => 'error', 'message' => 'Unique person ID is required']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debugging Form</title>
</head>
<body>
    <h2>Debugging Form</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="unique_person_id">Unique Person ID:</label>
        <input type="text" name="unique_person_id" id="unique_person_id"><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
