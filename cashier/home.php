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

    function get_queue() {
        $.ajax({
            url: './../Actions.php?a=next_queue',
            dataType: 'json',
            error: function(err) {
                console.log(err);
            },
            success: function(resp) {
                if (resp.status) {
                    if (Object.keys(resp.data).length > 0) {
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
    }

    $(function() {
        $('#next_queue').click(function() {
            get_queue();
        });
        $('#notify').click(function() {
            if (in_queue.queue) {
                update_queue_info(in_queue);
            } else {
                alert("No Queue Available");
            }
        });
    });



    var websocket = new WebSocket("ws://192.168.4.1:81/");
websocket.onopen = function(event) {
    console.log('Socket is open!');
};
websocket.onclose = function(event) {
    console.log('Socket has been closed!');
};
websocket.onmessage = function(event) {
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

</script>