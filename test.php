<?php
require_once('DBConnection.php');

$conn = new DBConnection();

// Step 1: Count total number of encrypted_id_numbers
$countSql = "SELECT COUNT(*) as total FROM `queue_list`";
$countResult = $conn->query($countSql);
$totalCount = $countResult->fetchArray(SQLITE3_ASSOC)['total'];

// Pagination variables
$perPage = 50000; // Number of records per page
$totalPages = ceil($totalCount / $perPage); // Total number of pages
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number, default to 1

// Calculate offset for SQL query
$offset = ($page - 1) * $perPage;

// Step 2: Fetch encrypted_id_numbers for the current page
$sql = "SELECT encrypted_id_number FROM `queue_list` LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);

// Initialize an array to store decrypted ID numbers
$decryptedIDNumbers = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $encryptedID = $row['encrypted_id_number'];
    $decryptedID = $conn->decrypt_data($encryptedID);
    if ($decryptedID !== false) {
        $decryptedIDNumbers[] = $decryptedID;
    }
}

// Close the DB connection
unset($conn);

// Function to output decrypted IDs in HTML
function outputDecryptedIDs($decryptedIDs) {
    echo '<h2>Decrypted ID Numbers</h2>';
    echo '<div class="grid-container">';
    foreach ($decryptedIDs as $id) {
        echo '<div class="grid-item">' . htmlspecialchars($id) . '</div>';
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paginated Decrypted ID Numbers</title>
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
            text-align: center;
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
        outputDecryptedIDs($decryptedIDNumbers);

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
