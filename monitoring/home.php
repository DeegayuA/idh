<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Queue Monitor</title>
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


    html,
    body {
      height: 100%;
      margin: 0;
      padding: 0;
      background: var(--background-color);
      color: var(--text-color);
      font-family: var(--font-family);
      overflow: hidden;
    }

    nav {
      margin-bottom: 0;
    }

    #monitor-holder {
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
      padding: 1rem 0rem 0rem 0rem !important;
    }

    .list-group-item {
      background: var(--card-background);
      color: var(--text-color);
      border-radius: 0.5rem !important;
      padding: 0.5rem;
      margin-bottom: 0.5rem;
    }

    .list-group-item .cashier-name {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--text-color);
    }

    .list-group-item .serve-queue {
      font-size: 2rem;
      color: var(--text-color);
    }

    .fs-5,
    .fs-4,
    .fs-1 {
      font-weight: bold;
    }

    hr {
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
      flex: 4;
      max-width: 40%;
    }

    #action-field {
      flex: 6;
      max-width: 60%;
    }

    @media (max-width: 992px) {
      #serving-field {
        max-width: 40%;
      }

      #action-field {
        max-width: 60%;
      }
    }

    .doctor-room {
      flex: auto auto auto;
      width: 400px;
      border-radius: 15px;
      overflow: hidden;
    }

    .card-custom {
      width: 100%;
      max-width: 400px;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .list-group-item[data-id="1"] {
      background-color: var(--doctor-1-color) !important;
    }

    .list-group-item[data-id="2"] {
      background-color: var(--doctor-2-color) !important;
    }

    .list-group-item[data-id="3"] {
      background-color: var(--doctor-3-color) !important;
    }

    .list-group-item[data-id="4"] {
      background-color: var(--doctor-4-color) !important;
    }

    .list-group-item[data-id="5"] {
      background-color: var(--doctor-5-color) !important;
    }



    .doctor-room:hover .card-custom {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .doctor-room .card-custom {
      transition: box-shadow 0.3s ease;
    }

    @media (max-width: 768px) {
      #action-field {
        display: none;
      }
    }

    .footer{
      padding: 0 5rem;
    }
    .footer-logos {
  text-align: center;
  margin-top: 2rem;
}

.footer-logos img {
  height: 50px;
  width: auto;
  margin: 10px;
}

.QR-logos img {
  height: 100px;
  aspect-ratio: 1;
}

video{
  height: 90%;
  aspect-ratio: 16/9;
}

  </style>
</head>

<body>
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
              <h5 class="text-center">Now Serving</h5>
            </div>
            <div class="card-body p-0">
              <div id="serving-list" class="list-group">
                <?php
                $cashier = $conn->query("SELECT * FROM `cashier_list` ORDER BY `name` ASC");
                $i = 1; // Initialize a counter for the room data-id
                while ($row = $cashier->fetchArray()) :
                ?>
                  <div class="list-group-item" data-id="<?php echo $row['cashier_id'] ?>" style="display:none">
                    <div class="fs-5 fw-2 cashier-name border-bottom border-info"><?php echo $row['name'] ?></div>
                    <div class="ps-4"><span class="serve-queue fs-4 fw-bold">1001 - John Smith</span></div>
                    <div class="doctor-room d-flex flex-column align-items-center center-content color-room" data-room="<?php echo $i; ?>">
                    </div>
                  </div>


                  <?php $i++; // Increment the counter for each room 
                  ?>
                <?php endwhile; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-9 d-flex border-3 flex-column align-items-center justify-content-center bg-dark bg-gradient text-light" id="action-field">
          <div class="col-auto flex-grow-1 center">
            <?php
            $vid = scandir('./../video');
            $video = isset($vid[2]) ? $vid[2] : "";
            ?>
            <video id="loop-vid" src="./../video/<?php echo $video ?>" loop muted></video>
          </div>

        </div>
      </div>
    </div>
    <div class="footer d-flex justify-content-between align-items-center py-4">
  <div id="datetimefield" class="text-center">
    <div class="fs-1 fw-bold time"></div>
    <div class="fs-5 fw-bold date"></div>
  </div>
  <div class="footer-logos">
    <span>Powered by EDIC UOK</span>
    <div class="d-flex align-items-center justify-content-center">
      <img src="./../logos/EDICWebLogo.png" alt="EDIC Web Logo">
      <img src="./../logos/university-of-kelaniya-logo.png" alt="University of Kelaniya Logo">
    </div>
  </div>
  <div class="QR-logos">
    <img src="./../logos/QR.png" alt="qr">
  </div>
</div>
  </div>

  <script>
    $(document).ready(() => {
      let websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'] ?>:2306/queuing/php-sockets.php");
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
          cashier_id: '<?php echo $_SESSION['cashier_id']; ?>',
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
        let vid_width = $('#action-field').width();
        $('#serving-field,#action-field').height(container_height - 250);
        $('#loop-vid').width(vid_width - 50);
        $('#serving-list').height($('#serving-list').parent().height() - 30);
      };

      const new_queue = (cashier_id, qid) => {
        console.log('Fetching Queue Data for Cashier ID:', cashier_id, 'Queue ID:', qid);
        $.ajax({
          url: './../Actions.php?a=get_queue',
          method: 'POST',
          data: {
            cashier_id,
            qid
          },
          dataType: 'JSON',
          error: err => console.log('Error Fetching Queue Data:', err),
          success: resp => {
            console.log('Queue Data Response:', resp);
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
          console.log('WebSocket Message Received:', event.data);
          let Data = JSON.parse(event.data);
          if (Data.type) {
            if (Data.type === 'queue') {
              console.log('Queue Data:', Data);
              new_queue(Data.cashier_id, Data.qid);
            }
            if (Data.type === 'test') {
              speak("This is a sample notification.");
            }

            // Handle color room display based on id
            if (Data.type === 'color') {
              let room = $(`#color-room-${Data.id}`).closest('.card-custom');
              room.show();
            }
          }
        };



      };

      const showMonitor = () => {
        $('#start').hide();
        $('#monitor-holder').removeClass('d-none');
        _resize_elements();
        $('#loop-vid')[0].play();

        // Hide all color rooms initially
        $('.color-room').hide();
      };

      $('#start').click(() => {
        showMonitor();
      });

      setup();
    });
  </script>

</body>

</html>