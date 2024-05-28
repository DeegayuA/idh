<?php
session_start();
require_once('DBConnection.php');

// Update all cashiers' status to not in-use
$db = new DBConnection();
$update_query = "UPDATE `cashier_list` SET log_status = 0";
$stmt = $db->prepare($update_query);

if($stmt->execute()) {
    // Status updated successfully
    echo json_encode(array('status' => 'success', 'msg' => 'All cashiers logged out temporarily.'));
} else {
    // Error updating status
    echo json_encode(array('status' => 'error', 'msg' => 'Failed to logout all cashiers.'));
}
?>
