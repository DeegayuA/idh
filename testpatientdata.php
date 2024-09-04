<?php
// Include the database connection file
require_once('DBConnection.php');

// Create an instance of the DBConnection class
$db = new DBConnection();

$results = [];
$test_unique_person_id = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['unique_person_id'])) {
    // Get the unique_person_id from the form input
    $unique_person_id = filter_input(INPUT_POST, 'unique_person_id', FILTER_SANITIZE_SPECIAL_CHARS);

    // Call the method and get the results
    try {
        $results = $db->getPatientDataByUniquePersonID($unique_person_id);
    } catch (Exception $e) {
        // Log and display the error
        error_log("Error fetching patient data: " . $e->getMessage());
        $results = ['error' => 'An error occurred while fetching data.'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Data Test</title>
</head>
<body>
    <h1>Test Patient Data by Unique Person ID</h1>
    <form method="post" action="">
        <label for="unique_person_id">Unique Person ID:</label>
        <input type="text" id="unique_person_id" name="unique_person_id" required>
        <input type="submit" value="Submit">
    </form>

    <?php if (isset($results['error'])): ?>
        <p><?php echo htmlspecialchars($results['error']); ?></p>
    <?php elseif (!empty($results)): ?>
        <h2>Patient Data:</h2>
        <table border="1">
            <tr>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Encrypted ID Number</th>
                <th>Date Created</th>
            </tr>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['encrypted_id_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <p>No data found for unique_person_id: <?php echo htmlspecialchars($unique_person_id); ?></p>
    <?php endif; ?>
</body>
</html>
