<?php
require_once('./phpqrcode/qrlib.php');

// Sample data to encode in the QR code
$sampleData = "Test QR Code Data";
$encryptedData = base64_encode($sampleData); // Simulate encryption if needed

// Directory to save the QR code image
$tempDir = './temp/';
$qrFileName = 'test_qrcode.png';
$qrFilePath = $tempDir . $qrFileName;

// Ensure the temp directory exists
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Generate the QR code
try {
    QRcode::png($encryptedData, $qrFilePath, QR_ECLEVEL_L, 4);
    if (file_exists($qrFilePath)) {
        echo "QR Code generated successfully: $qrFilePath\n";
    } else {
        echo "Failed to generate QR Code.\n";
    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
