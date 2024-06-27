<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cashier_id'])) {
    header("Location: ./login.php");
    exit;
}
require_once('./../DBConnection.php');
?>

<style>
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
    text-align: center;
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

.queue-info {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-top: 1px solid #ddd;
    padding-top: 1rem;
}

.queue-count {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
}

</style>

<div class="container full-height">
    <div class="row full-height center-content">
        <div class="col-md-6 d-flex flex-column align-items-center center-content">
            <div class="card shadow card-custom">
                <div class="card-header">
                    <h5 class="card-title text-center">Now Serving</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="card-title mb-0 fs-2" id="customer_name">Unknown</h3>
                        <div class="mt-2">
                            <span class="text-muted">Age:</span>
                            <span id="customer_age" class="mx-2">N/A</span>
                            <span class="text-muted">Sex:</span>
                            <span id="customer_sex" class="mx-2">N/A</span>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="fs-4 fw-bold mb-3">Queue Number</div>
                        <b><div class="fs-3 my-2" id="queue">----</div></b>
                    </div>
                    <div class="mt-4 w-100 text-center queue-info">
    <h6>Total Patients Waiting: <span id="total_patients_count" class="queue-count">Loading...</span></h6>
    <h6>Patients in Your Queue: <span id="specific_queue_count" class="queue-count">Loading...</span></h6>
</div>


                </div>
            </div>
        </div>
        <div class="col-md-6 d-flex flex-column align-items-center center-content">
            <div class="w-100">
                <button id="next_queue" class="btn btn-primary btn-custom"><i class="fa fa-forward"></i> Next</button>
            </div>
            <div class="w-100 mt-3">
                <button id="notify" class="btn btn-secondary btn-custom"><i class="fa fa-bullhorn"></i> Notify</button>
            </div>
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
    var cashier_id = <?php echo json_encode($_SESSION['cashier_id']); ?>;

    function get_queue() {
        $.ajax({
            url: './../Actions.php?a=next_queue',
            dataType: 'json',
            method: 'POST',
            data: { doctor_id: cashier_id },
            error: function(err) {
                console.log(err);
            },
            success: function(resp) {
                if (resp.status) {
                    if (resp.data && Object.keys(resp.data).length > 0) {
                        in_queue = resp.data;
                        update_queue_info(in_queue);
                    } else {
                        in_queue = {};
                        alert("No Queue Available");
                    }
                } else {
                    alert('An error occurred');
                }
            }
        });
    }

    function update_queue_info(queue_data) {
        $('#queue').text(queue_data.queue || "----");
        $('#customer_name').text(queue_data.customer_name || "Unknown");
        $('#customer_age').text(queue_data.age || "N/A");
        $('#customer_sex').text(queue_data.sex || "N/A");

        websocket.send(JSON.stringify({
            type: 'queue',
            cashier_id: '<?php echo $_SESSION['cashier_id'] ?>',
            qid: queue_data.queue_id
        }));

        updateQueueCounts(); // Update queue counts when new queue info is received
    }

    $(function() {
        $('#next_queue').click(function() {
            get_queue();
            updateQueueCounts();
        });
        $('#notify').click(function() {
            if (in_queue.queue) {
                update_queue_info(in_queue);
                updateQueueCounts();
            } else {
                alert("No Queue Available");
            }
        });

        // Function to update total patients waiting and specific queue counts
        function updateQueueCounts() {
            $.ajax({
                url: './../Actions.php?a=getQueueCounts',
                method: 'POST',
                dataType: 'json',
                success: function(resp) {
                    // Update total patients waiting
                    if (resp.total !== undefined) {
                        $('#total_patients_count').text(resp.total);
                    }
                    // Update specific doctor queue count if available
                    if (resp.doctors && resp.doctors['Room' + cashier_id]) {
                        $('#specific_queue_count').text(resp.doctors['Room' + cashier_id]);
                    } else {
                        $('#specific_queue_count').text(0);
                    }
                },
                error: function(err) {
                    console.error('Error fetching queue counts:', err);
                }
            });
        }

        // Initial call to update queue counts
        updateQueueCounts();
    });

    // ESP32 device integration (similar as before)
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
            if (message.action === "next_queue") {
                get_queue();
            } else if (message.action === "notify") {
                if (in_queue.queue) {
                    update_queue_info(in_queue);
                } else {
                    alert("No Queue Available");
                }
            }
        };
    } catch(err) {
        console.warn("ESP32 device not connected:", err);
    }
</script>