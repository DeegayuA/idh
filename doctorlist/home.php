<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('./../DBConnection.php');
$conn = new DBConnection();

// Fetch active cashier (doctor) IDs
$active_cashiers_response = $conn->get_active_cashiers();
$active_cashiers = json_decode($active_cashiers_response, true)['data'];
$active_cashier_count = count($active_cashiers);

// Define an array to hold doctor colors
$doctor_colors = array();

// Extract colors from CSS directly from the PHP file
$php_file = file_get_contents(__FILE__); 
preg_match_all('/--doctor-\d+-color: (.*?);/', $php_file, $matches);
if ($matches) {
    $doctor_colors = $matches[1];
}

// Calculate the number of doctor rooms to display
$doctor_room_count = min(10, $active_cashier_count); // Limit to maximum 10 rooms

// Prepare the JSON response
$response = array("data" => array());
for ($i = 0; $i < $doctor_room_count; $i++) {
    $response["data"][] = array(
        "name" => "Doctor Room " . ($i + 1),
        "color" => $doctor_colors[$i]
    );
}

?>

<style>
   :root {
    --doctor-1-color: red;
    --doctor-2-color: green;
    --doctor-3-color: blue;
    --doctor-4-color: yellow;
    --doctor-5-color: pink;
    --doctor-6-color: orange;
    --doctor-7-color: purple;
    --doctor-8-color: cyan;
    --doctor-9-color: teal;
    --doctor-10-color: magenta;
}

@media (prefers-color-scheme: dark) {
    :root {
        --doctor-1-color: darkred;
        --doctor-2-color: darkgreen;
        --doctor-3-color: darkblue;
        --doctor-4-color: yellow;
        --doctor-5-color: pink;
        --doctor-6-color: darkorange;
        --doctor-7-color: indigo;
        --doctor-8-color: darkcyan;
        --doctor-9-color: darkslategray;
        --doctor-10-color: darkmagenta;
    }
}


    .full-height {
        height: 100vh;
    }

    .center-content {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card-custom {
        width: 100%;
        max-width: 400px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-custom {
        width: 100%;
        border-radius: 15px;
        font-size: 1rem;
        padding: 10px;

    }

    .btn-primary {
        background-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
    }

    .btn-secondary {
        background-color: transparent;
        border: 1px solid var(--primary-color);
        transition: all 0.3s ease-in-out;
        color: var(--text-color);
    }

    .btn-secondary:hover {
        background-color: transparent;
        border: 2px solid var(--primary-hover);
        color: var(--text-color);
    }

    .gap {
        margin-left: 0.25rem;
    }

    .doctor-room {
        flex: auto auto auto;
        /* Allow elements to shrink but not grow */
        width: 400px;
        /* Set a fixed width for each doctor room */
        margin: 10px;
        /* Add some margin for spacing */
    }

    .button-section {
        margin: 10px;
    }
</style>
<div class="container full-height">
    <div class="row full-height center-content">
        <div class="d-flex flex-wrap ">
            <?php for ($i = 0; $i < $doctor_room_count; $i++) { ?>
                <div class="doctor-room mb-3 d-flex flex-column align-items-center center-content">
                    <div class="card shadow card-custom" style="background-color: var(--doctor-<?php echo $i + 1; ?>-color)">
                        <div class="card-header">
                            <h5 class="card-title text-center"><?php echo isset($active_cashiers[$i]['name']) ? $active_cashiers[$i]['name'] : 'Doctor Room ' . ($i + 1); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h3 class="card-title mb-0 fs-2" id="customer_name_<?php echo $i + 1; ?>">Unknown</h3>
                                <div class="mt-2">
                                    <span class="text-muted">Age:</span>
                                    <span id="customer_age_<?php echo $i + 1; ?>" class="mx-2">N/A</span>
                                    <span class="text-muted">Sex:</span>
                                    <span id="customer_sex_<?php echo $i + 1; ?>" class="mx-2">N/A</span>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <div class="fs-4 fw-bold mb-3">Queue Number</div>
                                <b>
                                    <div class="fs-3 my-2" id="queue_<?php echo $i + 1; ?>">----</div>
                                </b>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between ">
                            <button id="next_queue_<?php echo $i + 1; ?>" class="button-section btn btn-primary btn-custom next_queue"><i class="fa fa-forward"></i> Next</button>
                            <button id="notify_<?php echo $i + 1; ?>" class="button-section btn btn-secondary btn-custom notify"><i class="fa fa-bullhorn"></i> Notify</button>
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
    };
    websocket.onclose = function(event) {
        console.log('Socket has been closed!');
        websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
    };

    var in_queue = {};

    $(function() {
    // Handle click events for next buttons
    $('[id^=next_queue_]').click(function() {
        var doctorRoomNumber = $(this).attr('id').split('_')[2];
        console.log('Next Queue Button Clicked for Doctor Room:', doctorRoomNumber);
        get_queue(doctorRoomNumber);
    });

    // Handle click events for notify buttons
    $('[id^=notify_]').click(function() {
        var doctorRoomNumber = $(this).attr('id').split('_')[2];
        console.log('Notify Button Clicked for Doctor Room:', doctorRoomNumber);
        if (in_queue.queue) {
            update_queue_info(in_queue, doctorRoomNumber);
        } else {
            alert("No Queue Available");
        }
    });
});

function get_queue(doctorId) {
    $.ajax({
        url: './../Actions.php?a=next_queue',
        method: 'POST',
        data: { doctor_id: doctorId },
        dataType: 'json',
        error: function(err) {
            console.log(err);
        },
        success: function(resp) {
            if (resp.status) {
                if (Object.keys(resp.data).length > 0) {
                    in_queue = resp.data;
                    update_queue_info(in_queue, doctorId);
                } else {
                    alert("No Queue Available");
                }
            } else {
                alert('An error occurred');
            }
        }
    });
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

    console.log('Sending WebSocket Message:', message);
    websocket.send(message);
}

    // ESP32 WebSocket Integration
    try {
        var esp32_websocket = new WebSocket("ws://192.168.4.1:81/");
        esp32_websocket.onopen = function(event) {
            console.log('ESP Socket is open!');
        };
        esp32_websocket.onclose = function(event) {
            console.log('ESP Socket has been closed!');
        };
        esp32_websocket.onmessage = function(event) {
            var message = JSON.parse(event.data);
            var doctorRoomNumber = message.doctorRoomNumber;
            if (message.press === "single") {
                console.log('Notify Button Triggered for Doctor Room:', doctorRoomNumber);
                if (in_queue.queue) {
                    update_queue_info(in_queue, doctorRoomNumber);
                } else {
                    alert("No Queue Available");
                }
            } else if (message.press === "double") {
                console.log('Next Queue Button Triggered for Doctor Room:', doctorRoomNumber);
                get_queue(doctorRoomNumber);
            }
        };
    } catch(err) {
        console.warn("ESP32 device not connected:", err);
    }
</script>
