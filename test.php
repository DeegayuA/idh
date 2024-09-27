<?php
require_once('DBConnection.php');

$conn = new DBConnection();

// Step 1: Count total number of records
$countSql = "SELECT COUNT(*) as total FROM `queue_list`";
$countResult = $conn->query($countSql);
$totalCount = $countResult->fetchArray(SQLITE3_ASSOC)['total'];

// Pagination variables
$perPage = 50000; // Number of records per page
$totalPages = ceil($totalCount / $perPage); // Total number of pages
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number, default to 1

// Calculate offset for SQL query
$offset = ($page - 1) * $perPage;

// Step 2: Fetch all relevant fields for the current page
$sql = "SELECT queue_id, customer_name, age, sex, phone_number, encrypted_id_number, encrypted_unique_person_id FROM `queue_list` LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);

// Initialize an array to store decrypted data
$queueData = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // Decrypt each field that is encrypted
    $encryptedID = $row['encrypted_id_number'];
    $encryptedUniqueID = $row['encrypted_unique_person_id'];
    $encryptedName = $row['customer_name'];
    $encryptedPhoneNumber = $row['phone_number'];

    // Decrypt sensitive fields
    $decryptedID = $conn->decrypt_data($encryptedID);
    $decryptedUniqueID = $conn->decrypt_data($encryptedUniqueID);
    $decryptedName = $conn->decrypt_data($encryptedName);
    $decryptedPhoneNumber = $conn->decrypt_data($encryptedPhoneNumber);

    // Check if all decryptions were successful
    if ($decryptedID !== false && $decryptedUniqueID !== false && $decryptedName !== false && $decryptedPhoneNumber !== false) {
        $queueData[] = [
            'queue_id' => $row['queue_id'],
            'customer_name' => $decryptedName,
            'age' => $row['age'],  // Assuming age is not encrypted
            'sex' => $row['sex'],  // Assuming sex is not encrypted
            'phone_number' => $decryptedPhoneNumber,
            'decrypted_id_number' => $decryptedID,
            'decrypted_unique_person_id' => $decryptedUniqueID
        ];
    }
}

// Close the DB connection
unset($conn);

// Function to output decrypted data in HTML
function outputDecryptedQueueData($queueData) {
    echo '<h2>Decrypted Queue Data</h2>';
    echo '<div class="grid-container">';
    foreach ($queueData as $data) {
        echo '<div class="grid-item">';
        echo '<strong>ID:</strong> ' . htmlspecialchars($data['decrypted_id_number']) . '<br>';
        echo '<strong>Unique Person ID:</strong> ' . htmlspecialchars($data['decrypted_unique_person_id']) . '<br>';
        echo '<strong>Name:</strong> ' . htmlspecialchars($data['customer_name']) . '<br>';
        echo '<strong>Age:</strong> ' . htmlspecialchars($data['age']) . '<br>';
        echo '<strong>Sex:</strong> ' . htmlspecialchars($data['sex']) . '<br>';
        echo '<strong>Phone Number:</strong> ' . htmlspecialchars($data['phone_number']);
        echo '</div>';
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paginated Decrypted Queue Data</title>
    <style>
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .grid-item {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            font-size: 16px;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #f1f1f1;
            color: black;
            border: 1px solid #ccc;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination .active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        outputDecryptedQueueData($queueData);

        // Pagination links
        echo '<div class="pagination">';
        if ($page > 1) {
            echo '<a href="?page='.($page - 1).'">Previous</a>';
        }
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $page) ? 'active' : '';
            echo '<a class="'.$activeClass.'" href="?page='.$i.'">'.$i.'</a>';
        }
        if ($page < $totalPages) {
            echo '<a href="?page='.($page + 1).'">Next</a>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>
