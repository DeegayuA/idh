<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('./../DBConnection.php');
$conn = new DBConnection();

// Fetch active cashier (doctor) IDs and their queue counts
$active_cashiers_response = $conn->get_active_cashiers();
$active_cashiers = json_decode($active_cashiers_response, true)['data'];
$active_cashier_count = count($active_cashiers);

// Define an array to hold doctor colors
$doctor_colors = [];

// Extract colors from CSS directly from the PHP file
$php_file = file_get_contents(__FILE__);
preg_match_all('/--doctor-\d+-color: (.*?);/', $php_file, $matches);
if ($matches) {
    $doctor_colors = $matches[1];
}

// Calculate the number of doctor rooms to display
$doctor_room_count = min(10, $active_cashier_count); // Limit to maximum 10 rooms

// Prepare the JSON response for doctor rooms
$response = ["data" => []];
for ($i = 0; $i < $doctor_room_count; $i++) {
    // Extract doctor name
    $doctor_name = $active_cashiers[$i]['name'];

    $response["data"][] = [
        "name" => $doctor_name,
        "queue_count" => 0, // Initially set to 0, will be updated via AJAX
        "color" => $doctor_colors[$i]
    ];
}

// Output CSS changes for light and dark mode adjustments
echo "<style>";
foreach ($doctor_colors as $index => $color) {
    $btn_color = adjustBrightness($color, isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark' ? -10 : -35);
    $btn_hover_color = adjustBrightness($color, isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark' ? -30 : -50);

    echo "
        :root {
            --doctor-" . ($index + 1) . "-btn-color: {$btn_color};
            --doctor-" . ($index + 1) . "-btn-hover: {$btn_hover_color};
        }
    ";
}
echo "</style>";

function adjustBrightness($hex, $steps)
{
    $steps = max(-255, min(255, $steps));

    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color = hexdec($color);
        $color = max(0, min(255, $color + $steps));
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
    }

    return $return;
}
?>



<style>
    :root {
        --doctor-1-color: #E57373;
        /* Light Red */
        --doctor-2-color: #81C784;
        /* Light Green */
        --doctor-3-color: #64B5F6;
        /* Light Blue */
        --doctor-4-color: #FFF176;
        /* Light Yellow */
        --doctor-5-color: #F06292;
        /* Light Pink */
        --doctor-6-color: #FFB74D;
        /* Light Orange */
        --doctor-7-color: #9575CD;
        /* Light Purple */
        --doctor-8-color: #4DD0E1;
        /* Light Cyan */
        --doctor-9-color: #4DB6AC;
        /* Light Teal */
        --doctor-10-color: #BA68C8;
        /* Light Magenta */
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --doctor-1-color: #D32F2F;
            /* Dark Red */
            --doctor-2-color: #388E3C;
            /* Dark Green */
            --doctor-3-color: #1976D2;
            /* Dark Blue */
            --doctor-4-color: #FBC02D;
            /* Dark Yellow */
            --doctor-5-color: #E91E63;
            /* Dark Pink */
            --doctor-6-color: #F57C00;
            /* Dark Orange */
            --doctor-7-color: #512DA8;
            /* Dark Purple */
            --doctor-8-color: #0097A7;
            /* Dark Cyan */
            --doctor-9-color: #00796B;
            /* Dark Teal */
            --doctor-10-color: #8E24AA;
            /* Dark Magenta */
        }
    }

    .total-queue-banner {
        width: 100%;
        /* background-color: var(--card-background); */
        padding-top: 10px;
        margin: 10px;
        margin-bottom: 0;
        display: flex;
        justify-content: center;
    }

    .total-queue-banner h2 {
        margin: 0;
        color: var(--text-color);

    }

    .full-height {
        height: calc(100vh - 60px);
    }

    .center-content {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card-custom {
        width: 100%;
        max-width: 450px;
        border-radius: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-custom {
        width: 100%;
        border-radius: 15px;
        font-size: 1rem;
        padding: 10px;
        transition: all 0.3s ease-in-out;
        /* Added transition for smooth hover effect */
    }

    .gap {
        margin-left: 0.25rem;
    }

    .doctor-room {
        flex: auto auto auto;
        width: 400px;
        margin: 10px;
    }

    .button-section {
        margin: 10px;
    }

    .alert-banner {
        position: absolute;
        top: 70px;
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: var(--alert-background-color);
        padding: 0.7rem;
        color: #fff;
        border-radius: 0.5rem;
        opacity: 0;
        transition: opacity 0.8s ease-in-out;
    }

    .alert-banner.show {
        opacity: 1;
    }


    @media (prefers-color-scheme: light) {
        :root {
            --alert-background-color: #FF9800;
            /* Light theme orange (fallback) */
        }
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --alert-background-color: #FFC107;
            /* Dark theme amber (fallback) */
        }
    }


    @media screen and (prefers-color-scheme: light) {
        <?php for ($i = 1; $i <= $doctor_room_count; $i++) { ?>.btn-primary-<?php echo $i; ?> {
            background-color: var(--doctor-<?php echo $i; ?>-btn-color);
            filter: contrast(1.6) brightness(0.9);
        }

        .btn-primary-<?php echo $i; ?>:hover {
            background-color: var(--doctor-<?php echo $i; ?>-btn-hover);
            filter: contrast(1.3) brightness(1.2);
        }

        .btn-secondary-<?php echo $i; ?> {
            border: 2px solid var(--doctor-<?php echo $i; ?>-btn-color);
            color: var(--doctor-<?php echo $i; ?>-btn-color);
            /* Added text color for secondary button */
            filter: contrast(1.6) brightness(0.9);
        }

        .btn-secondary-<?php echo $i; ?>:hover {
            border: 2px solid var(--doctor-<?php echo $i; ?>-btn-hover);
            color: var(--doctor-<?php echo $i; ?>-btn-hover);
            /* Updated text color on hover */
            filter: contrast(1.3) brightness(1.2);
        }

        <?php } ?>
    }

    @media screen and (prefers-color-scheme: dark) {
        <?php for ($i = 1; $i <= $doctor_room_count; $i++) { ?>.btn-primary-<?php echo $i; ?> {
            background-color: var(--doctor-<?php echo $i; ?>-btn-color);
            filter: contrast(1.5) brightness(1.5);
        }

        .btn-primary-<?php echo $i; ?>:hover {
            background-color: var(--doctor-<?php echo $i; ?>-btn-hover);
            filter: contrast(1.6) brightness(1.4);
        }

        .btn-secondary-<?php echo $i; ?> {
            border: 2px solid var(--doctor-<?php echo $i; ?>-btn-color);
            color: var(--doctor-<?php echo $i; ?>-btn-color);
            /* Added text color for secondary button */
            filter: contrast(1.5) brightness(1.5);
        }

        .btn-secondary-<?php echo $i; ?>:hover {
            border: 2px solid var(--doctor-<?php echo $i; ?>-btn-hover);
            color: var(--doctor-<?php echo $i; ?>-btn-hover);
            /* Updated text color on hover */
            filter: contrast(1.6) brightness(1.4);
        }

        <?php } ?>
    }
</style>


<div class="container full-height">
    <div class="row full-height center-content">
        <div class="d-flex flex-wrap">
            <h3 id="alert-banner" class="alert-banner text-center container"></h3>
            <div class="banner total-queue-banner text-center">
                <h3>

                    Total Patients Waiting: <span id="total_patients_count">0</span>,
                    Monitor Connection: <span id="websocket_status"><span style="color:orange;">?</span></span>,
                    IoT Connection: <span id="esp32_status"><span style="color:orange;">?</span></span>
                </h3>
            </div>

            <?php for ($i = 1; $i <= $doctor_room_count; $i++) { ?>
                <div class="doctor-room mb-3 d-flex flex-column align-items-center center-content">
                    <div class="card shadow card-custom" style="background-color: var(--doctor-<?php echo $i; ?>-color)">
                        <div class="">
                            <h5 class="card-title text-center"><?php echo $response['data'][$i - 1]['name']; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h3 class="card-title mb-0 fs-2" id="customer_name_<?php echo $i; ?>">Unknown</h3>
                                <div class="mt-2">
                                    <span class="text fw-bold">Age:</span>
                                    <span id="customer_age_<?php echo $i; ?>" class="mx-2">N/A</span>
                                    <span class="text fw-bold">Sex:</span>
                                    <span id="customer_sex_<?php echo $i; ?>" class="mx-2">N/A</span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <div class="fs-4 fw-bold mb-3">Queue Information</div>
                                <div class="mb-2">
                                    <span class="text fw-light">Queue Number:</span>
                                    <span class="mx-2" id="queue_<?php echo $i; ?>">----</span>
                                </div>
                                <div>
                                    <span class="text fw-light">Patients in Queue:</span>
                                    <span class="mx-2" id="total_patients_<?php echo $i; ?>">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button id="next_queue_<?php echo $i; ?>" class="button-section btn btn-primary-<?php echo $i; ?> btn-custom next_queue">
                                <i class="fa fa-forward"></i> Next
                            </button>
                            <button id="notify_<?php echo $i; ?>" class="button-section btn btn-secondary-<?php echo $i; ?> btn-custom notify">
                                <i class="fa fa-bell"></i> Notify
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    var websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");

    websocket.onopen = function(event) {
        console.log('Socket is open!');
        $('#websocket_status').html('<span style="color:green;">&#x2713;</span>'); // Green arrow for open

    };

    websocket.onclose = function(event) {
        console.log('Socket has been closed!');
        websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
        $('#websocket_status').html('<span style="color:red;">&#10060;</span>'); // Red cross for closed

    };

    $(document).ready(function() {
        var in_queue = {};
        var alertQueue = [];
        var alertOpen = false;

        updateQueueCounts(); // Initial update

        $('[id^=next_queue_]').click(function() {
            var doctorRoomNumber = $(this).attr('id').split('_')[2];
            get_queue(doctorRoomNumber);
            updateQueueCounts();
        });

        $('[id^=notify_]').click(function() {
            var doctorRoomNumber = $(this).attr('id').split('_')[1];

            if (in_queue[doctorRoomNumber] && in_queue[doctorRoomNumber].queue) {
                update_queue_info(in_queue[doctorRoomNumber], doctorRoomNumber);
                updateQueueCounts();
            } else {
                queueAlert("No Queue Available for Doctor Room " + doctorRoomNumber, doctorRoomNumber);
            }
        });

        function queueAlert(message, doctorRoomNumber) {
            alertQueue.push({
                message: message,
                doctorRoomNumber: doctorRoomNumber
            });
            if (!alertOpen) {
                showNextAlert();
            }
        }

        function showNextAlert() {
            if (alertQueue.length > 0) {
                var alertData = alertQueue.shift();
                showAlertBanner(alertData.message, alertData.doctorRoomNumber);
            }
        }

        function showAlertBanner(message, doctorRoomNumber) {
            alertOpen = true;

            var doctorColor = getComputedStyle(document.documentElement).getPropertyValue(`--doctor-${doctorRoomNumber}-color`);
            var emojis = ["⚠️", "🚨", "❗"];
            var emoji = emojis[doctorRoomNumber % emojis.length];

            $('#alert-banner')
                .css('background-color', doctorColor)
                .html(`${emoji} ${message}`)
                .addClass('show')
                .fadeIn(300) // Ensure this aligns with your transition duration
                .delay(2000) // Duration to keep visible
                .fadeOut(500, function() {
                    $(this).removeClass('show');
                    alertOpen = false;
                    showNextAlert();
                });
        }

        function updateQueueCounts() {
            $.ajax({
                url: './../Actions.php?a=getQueueCounts',
                method: 'POST',
                dataType: 'json',
                success: function(resp) {
                    if (resp.total !== undefined) {
                        $('#total_patients_count').text(resp.total);
                    }
                    for (var i = 1; i <= Object.keys(resp.doctors).length; i++) {
                        var roomName = 'Room' + i;
                        var patientCount = resp.doctors[roomName];
                        $('#total_patients_' + i).text(patientCount);
                    }
                },
                error: function(err) {
                    console.error('Error fetching queue counts:', err);
                }
            });
        }

        function get_queue(doctorId) {
            $.ajax({
                url: './../Actions.php?a=next_queue',
                method: 'POST',
                data: {
                    doctor_id: doctorId
                },
                dataType: 'json',
                error: function(err) {
                    console.log(err);
                },
                success: function(resp) {
                    if (resp.status === 'success' && resp.data !== null) {
                        in_queue[doctorId] = resp.data;
                        update_queue_info(resp.data, doctorId);
                    } else {
                        in_queue[doctorId] = null;
                        resetQueueInfo(doctorId);
                        queueAlert("No Queue Available for Doctor Room " + doctorId, doctorId);
                    }
                }
            });
        }

        function resetQueueInfo(doctorId) {
            var queueElementId = '#queue_' + doctorId;
            var customerNameElementId = '#customer_name_' + doctorId;
            var customerAgeElementId = '#customer_age_' + doctorId;
            var customerSexElementId = '#customer_sex_' + doctorId;

            $(queueElementId).text("----");
            $(customerNameElementId).text("Unknown");
            $(customerAgeElementId).text("N/A");
            $(customerSexElementId).text("N/A");
        }

        function update_queue_info(queue_data, doctorId) {
            var queueElementId = '#queue_' + doctorId;
            var customerNameElementId = '#customer_name_' + doctorId;
            var customerAgeElementId = '#customer_age_' + doctorId;
            var customerSexElementId = '#customer_sex_' + doctorId;

            $(queueElementId).text(queue_data.queue || "----");
            $(customerNameElementId).text(queue_data.customer_name || "Unknown");
            $(customerAgeElementId).text(queue_data.age || "N/A");
            $(customerSexElementId).text(queue_data.sex || "N/A");

            const message = JSON.stringify({
                type: 'queue',
                cashier_id: doctorId,
                qid: queue_data.queue_id
            });

            websocket.send(message);
        }


        // ESP32 WebSocket Integration
        try {
            var esp32_websocket = new WebSocket("ws://IDHQ_by_EDIC.local:81/");

            esp32_websocket.onopen = function(event) {
                console.log('ESP Socket is open!');
                $('#esp32_status').html('<span style="color:green;">&#x2713;</span>'); // Green arrow for open
            };

            esp32_websocket.onclose = function(event) {
                console.log('ESP Socket has been closed!');
                $('#esp32_status').html('<span style="color:red;">&#10060;</span>'); // Red cross for closed
            };

            esp32_websocket.onmessage = function(event) {
                var message = JSON.parse(event.data);
                var doctorRoomNumber = message.doctorRoomNumber;

                if (message.press === "single") {
                    if (alertOpen) {
                        alertOpen = false; // Close the alert
                        console.log("Alert dismissed via IoT device");
                    } else if (in_queue[doctorRoomNumber] && in_queue[doctorRoomNumber].queue) {
                        update_queue_info(in_queue[doctorRoomNumber], doctorRoomNumber);
                        updateQueueCounts(); // Update the total patient count
                    } else {
                        showAlertBanner("No Queue Available", doctorRoomNumber);
                    }
                } else if (message.press === "double") {
                    get_queue(doctorRoomNumber);
                    updateQueueCounts(); // Update the total patient count after getting the next queue
                }
            };

        } catch (err) {
            console.warn("ESP32 device not connected:", err);
        }
    });
</script>