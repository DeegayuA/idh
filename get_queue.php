<?php
require_once('./DBConnection.php');
require_once('phpqrcode/qrlib.php');
date_default_timezone_set('Asia/Colombo');

// Generate the queue number
$queue = $conn->generateQueueNumber();

// Get the patient details if an ID is provided
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM `queue_list` WHERE `queue_id` = '{$_GET['id']}'");
    @$res = $qry->fetchArray();

    if ($res) {
        foreach ($res as $k => $v) {
            if (!is_numeric($k)) {
                $$k = $v;
            }
        }

        $customer_name = $conn->decrypt_data($customer_name);
        $phone_number = $conn->decrypt_data($phone_number);

        // Generate QR code from encrypted phone number
        $encryptedPhoneNumber = $conn->encrypt_data($phone_number);
        $tempDir = './temp/'; // Directory to save the QR code image
        $qrFileName = 'qrcode_' . md5($encryptedPhoneNumber) . '.png';
        $qrFilePath = $tempDir . $qrFileName;

        if (!file_exists($qrFilePath)) {
            QRcode::png($encryptedPhoneNumber, $qrFilePath, QR_ECLEVEL_L, 4);
        }

        // Get Patient History
        $patientHistory = $conn->getPatientHistory($phone_number, 5); // Limit to 5 entries

        // Calculate Estimated Waiting Time
        $qry = $conn->query("SELECT COUNT(*) as count FROM `queue_list` WHERE `status` = 0 AND strftime('%Y-%m-%d', `date_created`) = strftime('%Y-%m-%d', 'now') LIMIT 5");
        $row = $qry->fetchArray();
        $queuedPatients = $row['count']; // Number of patients in the queue with status = 0
        $estimatedWaitSeconds = $queuedPatients * 5 * 60; // Each patient takes approximately 5 minutes, converted to seconds
        $estimatedTime = date("H:i:s", strtotime("+" . $estimatedWaitSeconds . " seconds")); // Format estimated time as 20:59:00
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Queue Token</title>
    <link rel="stylesheet" href="./Font-Awesome-master/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./DataTables/datatables.min.css">
    <script src="./DataTables/datatables.min.js"></script>
    <script src="./Font-Awesome-master/js/all.min.js"></script>
    <style>
        :root {
            --background-color: #ffffff;
            --text-color: #000000;
            --card-background: #f8f9fa;
            --primary-color: #4b79a1;
            --primary-hover: #283e51;
            --border-radius: 10px;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            --font-family: 'Montserrat', sans-serif;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --background-color: #121212;
                --text-color: #ffffff;
                --card-background: #272727;
                --primary-color: #4b79a1;
                --primary-hover: #66a3d9;
            }
        }

        html,
        body {
            height: 100%;
            width: 100%;
            background: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            background-color: var(--background-color);
        }

        .container2 {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background: var(--card-background);
            padding: 20px;
            width: 100%;
            max-width: 500px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: var(--border-radius);
            width: 100%;
            padding: 10px;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .text-info2 {
            color: var(--primary-hover);
        }

        .form-control,
        .form-select {
            border-radius: var(--border-radius);
            border: none;
            border-bottom: 2px solid var(--primary-color);
            transition: border-color 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-hover);
            box-shadow: none;
        }

        .text-center {
            text-align: center;
        }

        .fs-1 {
            font-size: 2rem;
        }

        .fw-bold {
            font-weight: bold;
        }

        .history-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .history-table th,
        .history-table td {
            border: 1px solid var(--text-color);
            padding: 10px;
            text-align: left;
        }

        .history-table th {
            background-color: var(--primary-color);
        }

        .bill-header {
            margin-bottom: 20px;
        }

        .bill-footer {
            margin-top: 20px;
            border-top: 1px solid var(--text-color);
            padding-top: 10px;
            font-size: 0.9rem;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 2rem;
        }
        .btn-outline-primary {
        color: var(--primary-color);
        background-color: transparent;
        background-image: none;
        border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        color: #fff;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;

        }

        .btn-success {
            background-color: #28a745;
            color: #fff;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-dark {
            background-color: #343a40;
            color: #fff;
        }

        .btn-dark:hover {
            background-color: #23272b;
        }

        .primaryColor {
            color: var(--primary-color);
        }

        .btn.btn-sm.rounded-0.btn-primary {
            display: none;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .card2,
            .card2 * {
                visibility: visible;
            }

            .container2 {
                border: 2px solid #000;
                box-shadow: none;
                margin: 0;
                padding: 20px;
                width: 80mm;
                margin: 0;
                padding: 0;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .card2 {
                position: relative;
                border: 2px solid #000;
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            .text-center {
                margin-bottom: 10px;
            }

            .history-table th,
            .history-table td {
                border: 1px solid #000 !important;
                padding: 8px;
            }

            .btn-container {
                display: none;
            }

            .age-phone p {
                margin: 0;
                padding: 0;
            }


        }
    </style>
</head>

<body>
    <div class="container2">
        <div class="card2">
            <div class="text-center bill-header">
                <h2>National Institute of Infectious Diseases</h2>
                <h4>ජාතික බෝවන රෝග විද්‍යායතනය</h4>
                <p><strong>Patient Queue Token</strong></p>
                <img src="<?php echo $qrFilePath; ?>" alt="QR Code" />
            </div>
            <div class="fs-1 fw-bold text-center text-info"><?php echo $queue ?></div>
            <center>
                <p><strong>Name / නම:</strong> <?php echo $customer_name ?></p>
                <div class="age-phone">
                    <p><strong>Age / වයස:</strong> <?php echo $age ?></p>
                    <p><strong>Phone Number / දුරකථන අංකය:</strong> <?php echo $phone_number ?></p>
                </div>
                <p><strong>Sex / ස්ත්‍රී/පුරුෂ භාවය:</strong> <?php echo $sex ?></p>

                <?php if (count($patientHistory) > 1) : ?>
                    <br>
                    <h5 class="text-center primaryColor">Visit History / ඉතිහාසය</h5>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date / දිනය</th>
                                <th>Queue Number / පෝලිමේ අංකය</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patientHistory as $visit) : ?>
                                <tr>
                                    <td><?php
                                        $date = new DateTime($visit['date_created'], new DateTimeZone('UTC'));
                                        $date->setTimezone(new DateTimeZone('Asia/Colombo'));
                                        echo $date->format('Y-m-d H:i:s');
                                        ?></td>
                                    <td><?php echo $visit['queue']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </center>
            <div class="bill-footer text-center">
                <?php if ($estimatedTime) : ?>
                    <p class="primaryColor"><b>Estimated Time: <?php echo $estimatedTime; ?></b></p>
                <?php endif; ?>
                <p>Thank you for your patience / ඔබේ ඉවසීමට ස්තුතියි</p>
                <p id="generated-time">Generated on: <?php echo date("Y-m-d H:i:s"); ?></p>

            </div>
            <div class="btn-container">
                <button class="btn btn-success" onclick="window.print()">Print</button>
                <button class="btn btn-dark" onclick="printQR()">Print QR Only</button>
                <a href="index.php" class="btn btn-dark">Home</a>
            </div>
        </div>
    </div>

    <script>
    function printQR() {
        var qrCodeElement = document.querySelector('.bill-header img');
        var newWindow = window.open('', '_blank');
        newWindow.document.write('<html><head><title>Print QR Code</title></head><body>');
        newWindow.document.write(qrCodeElement.outerHTML);
        newWindow.document.write('</body></html>');
        newWindow.document.close();
        newWindow.print();
    }
</script>

    <script>
        $(function() {
            $('#print').click(function() {
                var printContents = $('.container2').html();
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                updatePrintTime();
                window.print();
                document.body.innerHTML = originalContents;
                $('#uni_modal').modal('hide');
            });

            function updatePrintTime() {
                var d = new Date();
                // Convert the date to the desired timezone
                var options = {
                    timeZone: 'Asia/Colombo'
                };
                var n = d.toLocaleString('en-US', options);
                document.getElementById("generated-time").textContent = "Generated on: " + n;
            }

            function updateEstimatedWaitTime() {
                $.ajax({
                    url: 'get_waiting_time.php', // PHP script to fetch waiting time
                    success: function(data) {
                        // Convert waiting time to hours and minutes
                        var hours = Math.floor(data / 60);
                        var minutes = data % 60;

                        // Format hours and minutes
                        var formattedTime = pad(hours, 2) + ":" + pad(minutes, 2) + ":00";

                        // Update the waiting time on the page
                        $('#estimated-wait-time').text(formattedTime);
                    }
                });
            }

            // Function to pad single digits with leading zeros
            function pad(num, size) {
                var s = num + "";
                while (s.length < size) s = "0" + s;
                return s;
            }

            // Call the function initially
            updateEstimatedWaitTime();

            // Call the function every 60 seconds for real-time updates
            setInterval(updateEstimatedWaitTime, 60000); // 60 seconds
        });
    </script>
</body>

</html>