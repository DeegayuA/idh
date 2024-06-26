<?php
// Include DBConnection.php to access getQueueCounts function
require_once('DBConnection.php');

// Create an instance of DBConnection
$conn = new DBConnection();

// Call getQueueCounts function to get JSON-encoded response
$response = $conn->getQueueCounts();

// Output JSON response
// header('Content-Type: application/json');
echo $response;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test next_queue Function</title>
</head>
<body>
    <h2>Test next_queue Function</h2>
    <form action="" method="post">
        <label for="doctor_id">Doctor ID:</label>
        <input type="text" id="doctor_id" name="doctor_id" required>
        <br><br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
<?php
// Include your class or functions file that contains next_queue() method
include_once './Actions.php'; // Adjust the path as per your file structure

// Assuming $_POST['doctor_id'] contains the doctor_id from the form submission
if (isset($_POST['doctor_id'])) {
    // Create an instance of your class that contains next_queue() method
    $actions = new Actions(); // Replace with your class name
    // Call next_queue() function and capture the output
    $result = $actions->next_queue();

    // Output the result
    echo "Output from next_queue(): <pre>";
    echo htmlentities($result); // Display JSON response
    echo "</pre>";
} else {
    echo "Doctor ID is not provided.";
}
?>
