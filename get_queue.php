<?php
require_once('./DBConnection.php');
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

        // Get Patient History
        $patientHistory = $conn->getPatientHistory($phone_number);

        // Calculate Estimated Waiting Time
        $qry = $conn->query("SELECT COUNT(*) as count FROM `queue_list` WHERE `status` = 0");
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
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container2 {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .history-table th {
            background-color: #f2f2f2;
        }

        .bill-header {
            margin-bottom: 20px;
        }

        .bill-footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 0.9rem;
        }

        .btn-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
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

        @media print {
            body {
                background-color: #fff;
            }

            .container2 {
                border: 2px solid #000;
                box-shadow: none;
                margin: 0;
                padding: 20px;
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
        <div class="text-center bill-header">
            <h2>National Institute of Infectious Diseases</h2>
            <h4>ජාතික බෝවන රෝග විද්‍යායතනය</h4>
            <p><strong>Patient Queue Token</strong></p>
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
                <h5 class="text-center">Visit History / ඉතිහාසය</h5>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Queue Number / පෝලිමේ අංකය</th>
                            <th>Date / දිනය</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patientHistory as $visit) : ?>
                            <tr>
                                <td><?php echo $visit['queue']; ?></td>
                                <td><?php
                                    $date = new DateTime($visit['date_created'], new DateTimeZone('UTC'));
                                    $date->setTimezone(new DateTimeZone('Asia/Colombo'));
                                    echo $date->format('Y-m-d H:i:s');
                                    ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </center>
        <div class="bill-footer text-center">
            <?php if ($estimatedTime) : ?>
                <p><b>Estimated Waiting Time: <?php echo $estimatedTime; ?></b></p>
            <?php endif; ?>
            <p>Thank you for your patience / ඔබේ ඉවසීමට ස්තුතියි</p>
            <p id="generated-time">Generated on: <?php echo date("Y-m-d H:i:s"); ?></p>

        </div>
    </div>
    <div class="btn-container">
        <button class="btn btn-success" id="print" type="button"><i class="fa fa-print"></i> Print</button>
        <button class="btn btn-dark" data-bs-dismiss="modal" type="button"><i class="fa fa-times"></i> Close</button>
    </div>
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