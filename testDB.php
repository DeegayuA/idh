<?php
ini_set('memory_limit', '1G'); // Increase memory limit
set_time_limit(0); // Disable time limit (not recommended for production)

require_once('dbconnection.php');

// Function to generate a single random data entry
function generateRandomDataEntry() {
    $firstNames = ["John", "Jane", "Michael", "Emily", "Chris", "Anna"];
    $lastNames = ["Smith", "Doe", "Johnson", "Brown", "Williams", "Jones"];
    $sexes = ["Male", "Female"];
    
    $fullName = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    $username = strtolower(str_replace(' ', '', $fullName)) . rand(100, 999);
    $password = md5("password" . rand(100, 999));
    $age = rand(18, 99);
    $phoneNumber = '+94' . rand(700000000, 799999999);
    $idNumber = substr(str_shuffle("0123456789"), 0, 5);
    $uniquePersonID = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 12);

    return [
        'fullname' => $fullName,
        'username' => $username,
        'password' => $password,
        'age' => $age,
        'phone_number' => $phoneNumber,
        'id_number' => $idNumber,
        'unique_person_id' => $uniquePersonID,
        'customer_name' => $fullName,
        'sex' => $sexes[array_rand($sexes)]
    ];
}

// Function to insert data in batches
function insertDataInBatches($numRows, $batchSize) {
    global $conn;
    $rowsInserted = 0;

    for ($i = 0; $i < $numRows; $i += $batchSize) {
        $conn->exec('BEGIN'); // Start a transaction for batch insert

        for ($j = 0; $j < $batchSize && $rowsInserted < $numRows; $j++, $rowsInserted++) {
            $entry = generateRandomDataEntry();

            // Encrypt sensitive data
            $encryptedPhoneNumber = $conn->encrypt_data($entry['phone_number']);
            $encryptedName = $conn->encrypt_data($entry['customer_name']);
            $encryptedIDNumber = $conn->encrypt_data($entry['id_number']);
            $encryptedUniquePersonID = $conn->encrypt_data($entry['unique_person_id']);

            // Insert into queue_list
            $stmtQueue = $conn->prepare("INSERT INTO `queue_list` (`queue`, `customer_name`, `status`, `age`, `sex`, `phone_number`, `encrypted_id_number`, `encrypted_unique_person_id`) 
                                VALUES (?, ?, 1, ?, ?, ?, ?, ?)");
            $stmtQueue->bindValue(1, 'Q' . rand(1000, 9999), SQLITE3_TEXT);
            $stmtQueue->bindValue(2, $encryptedName, SQLITE3_TEXT);
            $stmtQueue->bindValue(3, $entry['age'], SQLITE3_INTEGER);
            $stmtQueue->bindValue(4, $entry['sex'], SQLITE3_TEXT);
            $stmtQueue->bindValue(5, $encryptedPhoneNumber, SQLITE3_TEXT);
            $stmtQueue->bindValue(6, $encryptedIDNumber, SQLITE3_TEXT);
            $stmtQueue->bindValue(7, $encryptedUniquePersonID, SQLITE3_TEXT);
            $stmtQueue->execute();

            // Insert into qrcode_list
            $stmtQRCode = $conn->prepare("INSERT INTO `qrcode_list` (`encrypted_unique_person_id`, `file_name`) VALUES (?, ?)");
            $stmtQRCode->bindValue(1, $encryptedUniquePersonID, SQLITE3_TEXT);
            $stmtQRCode->bindValue(2, 'file_' . rand(1000, 9999) . '.png', SQLITE3_TEXT);
            $stmtQRCode->execute();
        }

        $conn->exec('COMMIT'); // Commit the transaction

        // Send progress to JavaScript client
        echo "<script>updateProgress($rowsInserted, $numRows);</script>";
        flush();
        ob_flush();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numRows = intval($_POST['numRows']);
    $batchSize = 1000; // Adjust batch size as needed

    if ($numRows > 0) {
        echo "Starting insertion of $numRows rows in batches of $batchSize...<br>";
        echo '<div id="progress" style="width: 100%; background-color: #ddd;">';
        echo '<div id="progress-bar" style="width: 0%; height: 30px; background-color: #4CAF50;"></div>';
        echo '</div>';
        echo '<br>';

        ob_flush(); // Send output to browser

        // Output JavaScript to update progress bar
        echo '<script>';
        echo 'function updateProgress(current, total) {';
        echo '  var progressBar = document.getElementById("progress-bar");';
        echo '  var percent = Math.floor((current / total) * 100);';
        echo '  progressBar.style.width = percent + "%";';
        echo '  progressBar.innerHTML = percent + "%";';
        echo '}';
        echo '</script>';
        
        insertDataInBatches($numRows, $batchSize);

        echo "<br>All $numRows rows have been added.";
    } else {
        echo "Please enter a valid number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Database</title>
</head>
<body>
    <h1>Test Database Stability</h1>
    <form method="post">
        <label for="numRows">Number of Rows to Add:</label>
        <input type="number" id="numRows" name="numRows" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
