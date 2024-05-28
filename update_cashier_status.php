<?php
session_start();
require_once('DBConnection.php');

// Check if cashier_id is set in POST data
if(isset($_POST['cashier_id'])) {
    $cashier_id = $_POST['cashier_id'];
    
    // Update cashier status in the database
    $db = new DBConnection();
    $update_query = "UPDATE `cashier_list` SET log_status = 0 WHERE cashier_id = :cashier_id";
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':cashier_id', $cashier_id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        // Status updated successfully
        echo json_encode(array('status' => 'success', 'msg' => 'Cashier status updated.'));
    } else {
        // Error updating status
        echo json_encode(array('status' => 'error', 'msg' => 'Failed to update cashier status.'));
    }
} else {
    // Error: cashier_id not set
    echo json_encode(array('status' => 'error', 'msg' => 'Cashier ID not provided.'));
}
?>
