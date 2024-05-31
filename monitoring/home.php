
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        #monitor-holder {
            background-color: #f8f9fa; /* Subtle background color for the "Now Serving" card */
        }
        .center {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>

<div class="container-fluid">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <button class="btn btn-lg btn-primary w-100" id="start" type="button">Start Live Queue Monitor</button>
        </div>
    </div>
    <div class="border-dark border-3 border shadow d-none mt-3" id="monitor-holder">
        <div class="row my-0 mx-0">
            <div class="col-md-5 d-flex flex-column align-items-center justify-content-center border-end border-dark" id="serving-field">
                <div class="card col-sm-12 shadow">
                    <div class="card-header">
                        <h5 class="card-title text-center">Now Serving</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="serving-list" class="list-group overflow-auto">
                            <?php 
                            $cashier = $conn->query("SELECT * FROM `cashier_list` ORDER BY `name` ASC");
                            while($row = $cashier->fetchArray()):
                            ?>
                            <div class="list-group-item" data-id="<?php echo $row['cashier_id'] ?>" style="display:none">
                                <div class="fs-5 fw-2 cashier-name border-bottom border-info"><?php echo $row['name'] ?></div>
                                <div class="ps-4"><span class="serve-queue fs-4 fw-bold">1001 - John Smith</span></div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 d-flex flex-column align-items-center justify-content-center bg-dark bg-gradient text-light" id="action-field">
                <div class="col-auto flex-grow-1 center">
                    <?php 
                    $vid = scandir('./../video');
                    $video = isset($vid[2]) ? $vid[2]: "";
                    ?>
                    <video id="loop-vid" src="./../video/<?php echo $video ?>" loop muted class="w-100 h-100"></video>
                </div>
                <div id="datetimefield" class="w-100 col-auto">
                    <div class="fs-1 text-center time fw-bold"></div>
                    <div class="fs-5 text-center date fw-bold"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
    websocket.onopen = () => console.log('Socket is open!');
    websocket.onclose = () => {
        console.log('Socket has been closed!');
        websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
    };
    var in_queue = {};

    const get_queue = () => {
        $.ajax({
            url: './../Actions.php?a=next_queue',
            dataType: 'json',
            error: err => console.log(err),
            success: resp => {
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

    const update_queue_info = queue_data => {
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

    $(() => {
        $('#next_queue').click(() => get_queue());
        $('#notify').click(() => {
            if (in_queue.queue) {
                update_queue_info(in_queue);
            } else {
                alert("No Queue Available");
            }
        });
    });

    let tts = new SpeechSynthesisUtterance();
    tts.lang = "en";
    tts.voice = window.speechSynthesis.getVoices()[0];
    let notif_audio = new Audio("./../audio/ascend.mp3");
    let vid_loop = $('#loop-vid')[0];
    tts.onstart = () => vid_loop.pause();
    notif_audio.setAttribute('muted', true);
    notif_audio.setAttribute('autoplay', true);
    document.body.appendChild(notif_audio);

    const speak = (text = "") => {
        if (text == '') return false;
        tts.text = text;
        notif_audio.setAttribute('muted', false);
        notif_audio.play();
        setTimeout(() => {
            window.speechSynthesis.speak(tts);
            tts.onend = () => vid_loop.play();
        }, 500);
    }

    const time_loop = () => {
        const mos = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        let datetime = new Date();
        let hour = datetime.getHours();
        let min = datetime.getMinutes();
        let s = datetime.getSeconds();
        let ampm = hour >= 12 ? "PM" : "AM";
        let mo = mos[datetime.getMonth()];
        let d = datetime.getDate();
        let yr = datetime.getFullYear();
        hour = hour >= 12 ? hour - 12 : hour;
        hour = String(hour).padStart(2, '0');
        min = String(min).padStart(2, '0');
        s = String(s).padStart(2, '0');
        $('.time').text(`${hour}:${min}:${s} ${ampm}`);
        $('.date').text(`${mo} ${d}, ${yr}`);
    }

    const _resize_elements = () => {
        let window_height = $(window).height();
        let nav_height = $('nav').height();
        let container_height = window_height - nav_height;
        $('#serving-field,#action-field').height(container_height - 50);
        $('#serving-list').height($('#serving-list').parent().height() - 30);
    }

    const new_queue = (cashier_id, qid) => {
        $.ajax({
            url: './../Actions.php?a=get_queue',
            method: 'POST',
            data: { cashier_id, qid },
            dataType: 'JSON',
            error: err => console.log(err),
            success: resp => {
                if (resp.status === 'success') {
                    let item = $(`#serving-list .list-group-item[data-id="${cashier_id}"]`);
                    let cashier = item.find('.cashier-name').text();
                    let nitem = item.clone();
                    nitem.find('.serve-queue').text(`${resp.queue} - ${resp.name}`);
                    item.remove();
                    $('#serving-list').prepend(nitem);
                    if (resp.queue === '') {
                        nitem.hide('slow');
                    } else {
                        nitem.show('slow');
                        speak(`Queue Number ${Math.abs(resp.queue)} ${resp.name}, Please proceed to ${cashier}`);
                    }
                }
            }
        });
    }

    $(() => {
        setInterval(time_loop, 1000);
        $('#start').click(() => {
            $(this).hide();
            $('#monitor-holder').removeClass('d-none');
            _resize_elements();
            vid_loop.play();
        });
        $(window).resize(_resize_elements);

        websocket.onmessage = event => {
            let Data = JSON.parse(event.data);
            if (Data.type) {
                if (Data.type === 'queue') {
                    new_queue(Data.cashier_id, Data.qid);
                }
                if (Data.type === 'test') {
                    speak("This is a sample notification.");
                }
            }
        }
    });
</script>