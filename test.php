<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>searchByQRCodeFileName</title>
</head>
<body>
    <h2>searchByQRCodeFileName</h2>
    <form action="test.php" method="post">
        <label for="encrypted_unique_person_id">Encrypted Unique Person ID:</label><br>
        <input type="text" id="encrypted_unique_person_id" name="encrypted_unique_person_id" required><br><br>
        <button type="submit" name="submit">Update</button>
    </form>

    <?php
    // Include the DBConnection class
    require_once('DBConnection.php');

    // Check if the form is submitted
    if (isset($_POST['submit'])) {
        // Get the encrypted unique person ID from the form
        $encrypted_unique_person_id = $_POST['encrypted_unique_person_id'];

        // Create a new instance of DBConnection
        $conn = new DBConnection();

        // Call the searchByQRCodeFileName function and display the result
        $result = $conn->searchByQRCodeFileName($encrypted_unique_person_id);

        // Output the result for debugging
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
    ?>
</body>
</html>
