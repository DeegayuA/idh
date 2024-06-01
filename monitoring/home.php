
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #000000;
            --card-background: #fff;
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

        html, body, .container-fluid {
            height: 100%;
            margin: 0;
            padding: 0;
            background: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-family);
        }

        nav {
            margin-bottom: 0;
        }

        #monitor-holder {
            min-height: 100vh;
            background-color: var(--background-color);
        }

        .center {
            display: flex;
            justify-content: center;
            align-items: center;
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

        .card {
            background: var(--card-background);
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
        }

        .card-header {
            background: var(--card-background);
        }

        .list-group-item {
            background: var(--card-background);
            color: var(--text-color);
            border: 1px solid var(--primary-color);
            border-radius: 1rem;
        }

        .fs-5, .fs-4, .fs-1 {
            font-weight: bold;
        }
        hr{
            color: var(--primary-color);
        }

        .text-center {
            text-align: center;
        }

        .border-dark {
            border-color: #000000 !important;
        }

        .bg-dark {
            background-color: #000000 !important;
        }

        .bg-gradient {
            background-image: linear-gradient(45deg, #4b79a1, #283e51);
        }
        #serving-field {
        /* Default width for PC view */
        flex: 1;
        max-width: 25%;
    }
    #action-field {
        /* Default width for PC view */
        flex: 3;
        max-width: 75%;
    }

    /* Tablet View */
    @media (max-width: 992px) {
        #serving-field {
            /* Adjust width for tablet view */
            max-width: 40%;
        }
        #action-field {
            /* Adjust width for tablet view */
            max-width: 60%;
        }
    }

    /* Mobile View */
    @media (max-width: 768px) {
        #action-field {
            /* Hide action field in mobile view */
            display: none;
        }
    }
    </style>


<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <button class="btn btn-lg btn-primary w-100 mt-5" id="start" type="button">Start Live Queue Monitor</button>
        </div>
    </div>
    <div class="border-dark border-3 border shadow d-none" id="monitor-holder">
        <div class="row my-4 mx-0">
            <div class="col-md-3 d-flex flex-column align-items-center justify-content-center border-end border-dark" id="serving-field">
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
            <div class="col-md-9 d-flex border-3 flex-column align-items-center justify-content-center bg-dark bg-gradient text-light" id="action-field">
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
$(document).ready(() => {
  const websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
  websocket.onopen = () => console.log('Socket is open!');
  websocket.onclose = () => {
    console.log('Socket has been closed!');
    websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
  };

  let in_queue = {};

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
  };

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
  };

  const speak = (text = "") => {
    if (text == '') return false;
    let tts = new SpeechSynthesisUtterance();
    tts.lang = "en";
    tts.voice = window.speechSynthesis.getVoices()[0];
    tts.text = text;
    let notif_audio = new Audio("./../audio/ascend.mp3");
    notif_audio.muted = false;
    notif_audio.autoplay = true;
    document.body.appendChild(notif_audio);
    notif_audio.play();
    tts.onstart = () => $('#loop-vid')[0].pause();
    setTimeout(() => {
      window.speechSynthesis.speak(tts);
      tts.onend = () => $('#loop-vid')[0].play();
    }, 500);
  };

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
  };

  const _resize_elements = () => {
    let window_height = $(window).height();
    let nav_height = $('nav').height();
    let container_height = window_height - nav_height;
    $('#serving-field,#action-field').height(container_height - 50);
    $('#serving-list').height($('#serving-list').parent().height() - 30);
  };

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
  };

  const setup = () => {
    setInterval(time_loop, 1000);
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
    };
  };

  const showMonitor = () => {
    $('#start').hide();
    $('#monitor-holder').removeClass('d-none');
    _resize_elements();
    $('#loop-vid')[0].play();
  };

  $('#start').click(() => {
    showMonitor();
  });

  setup();
});
</script>

