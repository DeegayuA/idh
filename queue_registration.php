<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once('DBConnection.php');
require_once('config.php');
    
// Define the $page variable
$page = isset($page) ? $page : 'Home'; // Provide a default value

$title = ucwords(str_replace('_', ' ', $page));

$conn = new DBConnection();
$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : '';
$sex = isset($_POST['sex']) ? $_POST['sex'] : '';
$encrypted_id_number = isset($_POST['encrypted_id_number']) ? $_POST['encrypted_id_number'] : '';
$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
$encrypted_unique_person_id = isset($_POST['encrypted_unique_person_id']) ? $_POST['encrypted_unique_person_id'] : '';

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucwords(str_replace('_', ' ', $page)) ?> | IDH Queuing System</title>
    <link rel="stylesheet" href="./Font-Awesome-master/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./select2/css/select2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./DataTables/datatables.min.css">
    <script src="./DataTables/datatables.min.js"></script>
    <script src="./Font-Awesome-master/js/all.min.js"></script>
    <script src="./select2/js/select2.min.js"></script>
    <script src="./js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js" integrity="sha512-r6rDA7W6ZeQhvl8S7yRVQUKVHdexq+GAlNkNNqVC7YyIV+NwqCTJe2hDWCiffTyRNOeGEzRRJ9ifvRm/HCzGYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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

        .navbar {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--primary-color);
        }

        .container {
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

        .btn-secondary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-secondary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .form-control {
            border-radius: var(--border-radius);
            border: none;
            border-bottom: 2px solid var(--primary-color);
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-hover);
            box-shadow: none;
        }

        .text-info2 {
            color: var(--primary-hover);
        }

        .form-select {
            border-radius: var(--border-radius);
            border: none;
            border-bottom: 2px solid var(--primary-color);
            transition: border-color 0.2s;
        }

        .form-select:focus {
            border-color: var(--primary-hover);
            box-shadow: none;
        }

        .h5.card-title {
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
        }

        .alert {
            border-radius: var(--border-radius);
        }

        .footer-logos {
            text-align: center;
            margin-top: 2rem;
        }

        .brakeline {
            color: var(--primary-color);
        }

        .footer-logos img {
            height: 50px;
            width: auto;
            margin: 10px;
        }

        /* Custom styling for radio buttons */
        /* Wrap radio buttons in rows */
        #doctor-list {
            display: flex;
            flex-wrap: wrap;
        }

        #doctor-list label {
            margin-right: 10px;
        }

        /* Style for each radio button wrapper */
        .form-check {
            margin-bottom: 10px;
            /* Adjust as needed */
        }

        #none {
            margin-bottom: 10px;
            margin-right: 10px;
        }
        .header-text {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
        }

        .header-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
        <div class="container">
            <a class="navbar-brand" href="./">
                National Institute of Infectious Diseases, Sri Lanka | Patient Registration
            </a>
        </div>
    </nav>
    <div class="container">
        <?php
        if (isset($_SESSION['flashdata'])) :
        ?>
            <div class="dynamic_alert alert alert-<?php echo $_SESSION['flashdata']['type'] ?>">
                <div class="float-end"><a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="$(this).closest('.dynamic_alert').hide('slow').remove()">x</a></div>
                <?php echo $_SESSION['flashdata']['msg'] ?>
            </div>
            <?php unset($_SESSION['flashdata']) ?>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
            <h1 class="header-text">
            <div class="logo-container">
                <img src="./logos/IDH_logo.png" alt="IDH Logo" class="header-logo">
            </div>
            National Institute of Infectious Diseases, Sri Lanka
        </h1>
                <div class="h5 card-title">Get your Queue Number Here</div>
                <form action="" id="queue-form">
                    <div class="form-group mb-3">
                        <label for="encrypted_id_number" class="control-label text-info2">Enter NIC/Passport Number</label>
                        <div class="input-group">
                            <input type="text" id="encrypted_id_number" name="encrypted_id_number" autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                            <button type="button" class="btn btn-secondary" id="scan-qr-btn">Scan QR</button>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="phone_number" class="control-label text-info2">Enter Phone Number</label>
                        <div class="input-group">
                            <input type="text" id="phone_number" name="phone_number" autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                            <!-- <button type="button" class="btn btn-secondary" id="scan-qr-btn">Scan QR</button> -->
                        </div>
                    </div>
                    <div id="qr-reader" style="width: 100%; display: none;"></div>
                    <div class="form-group mb-3">
                        <label for="customer_name" class="control-label text-info2">Enter your Name</label>
                        <input type="text" id="customer_name" name="customer_name" autofocus autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="age" class="control-label text-info2">Enter your Age</label>
                        <input type="number" id="age" name="age" autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="sex" class="control-label text-info2">Select Sex</label>
                        <select name="sex" id="sex" class="form-select rounded-0 border-0 border-bottom" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="control-label text-info2">Select Preferred Doctor</label>
                        <div id="doctor-list">
                            <!-- Doctor list will be dynamically populated here -->
                        </div>
                    </div>

                    <input type="hidden" name="encrypted_unique_person_id" value="<?php echo htmlspecialchars($encrypted_unique_person_id); ?>">
                    <div class="form-group text-center my-2">
                        <button class="btn-primary btn-lg btn col-sm-4 rounded-0" type='submit'>Get Queue</button>
                    </div>
                    <div class="footer-logos">
                        <span>Powered by EDIC UOK</span>
                        <div class="logos">
                            <img src="./logos/EDICWebLogo.png" alt="EDIC Web Logo">
                            <img src="./logos/university-of-kelaniya-logo.png" alt="University of Kelaniya Logo">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uni_modal" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-sm rounded-0 btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content rounded-0">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Confirmation</h5>
                </div>
                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-primary btn-sm rounded-0" id='confirm' onclick="">Continue</button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $('#phone_number, #encrypted_id_number').on('blur', function() {
                var phoneNumber = $('#phone_number').val();
                var nicNumber = $('#encrypted_id_number').val();
                var uniquePersonId = $('#encrypted_unique_person_id').val();

                // // Check if both fields are non-empty
                // if (phoneNumber && nicNumber) {
                //     // Clear the phone number field to prioritize NIC/Passport number
                //     $('#phone_number').val('');
                // }

                // Fetch patient data
                fetchPatientData(phoneNumber, nicNumber, uniquePersonId);
            });

            // Function to fetch patient data
            function fetchPatientData(phoneNumber, nicNumber, uniquePersonId = '') {
                $.ajax({
                    url: 'fetch_patient_data.php',
                    method: 'POST',
                    data: {
                        phone_number: phoneNumber,
                        nic: nicNumber,
                        unique_person_id: uniquePersonId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            // Populate input fields with fetched data
                            $('#customer_name').val(data.customer_name);
                            $('#age').val(data.age);
                            $('#sex').val(data.sex);
                            $('#encrypted_id_number').val(data.encrypted_id_number);
                            $('#phone_number').val(data.phone_number);
                            // Add other fields to populate as necessary
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error fetching patient data:', textStatus, errorThrown);
                        alert('Error fetching patient data: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }

            function fetchActiveCashiers() {
                $.ajax({
                    url: './Actions.php?a=get_active_cashiers',
                    method: 'GET',
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.status === 'success') {
                            var cashiers = response.data;
                            var doctorList = $('#doctor-list').empty();
                            doctorList.append($('<input>').attr({
                                type: 'radio',
                                name: 'preferred_doctor',
                                id: 'none',
                                value: '0',
                                checked: 'checked'
                            }).add($('<label>').attr('for', 'none').text('None')));

                            cashiers.forEach(function(cashier) {
                                var radioBtn = $('<input>').attr({
                                    type: 'radio',
                                    name: 'preferred_doctor',
                                    id: 'doctor_' + cashier.cashier_id,
                                    value: cashier.cashier_id,
                                    class: 'form-check-input'
                                });
                                var label = $('<label>').attr('for', 'doctor_' + cashier.cashier_id).text(cashier.name);
                                var wrapper = $('<div>').addClass('form-check').append(radioBtn).append(label);
                                doctorList.append(wrapper);
                            });
                        } else {
                            console.log('Error fetching active cashiers:', response.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error fetching active cashiers:', textStatus, errorThrown);
                    }
                });
            }

            $('#queue-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                form.find('.pop-msg').remove();
                var el = $('<div>').addClass('alert pop-msg').hide();
                form.find('button[type="submit"]').attr('disabled', true);
                $.ajax({
                    url: './Actions.php?a=save_queue',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'JSON',
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error saving data:', textStatus, errorThrown);
                        el.addClass('alert-danger').text("An error occurred while saving data: " + textStatus + ' - ' + errorThrown);
                        form.find('button[type="submit"]').attr('disabled', false);
                        form.prepend(el);
                        el.show('slow');
                    },
                    success: function(resp) {
                        if (resp.status == 'success') {
                            uni_modal("Your Queue", "get_queue.php?success=true&id=" + resp.id);
                            $('#uni_modal').on('hide.bs.modal', function(e) {
                                location.reload();
                            });
                        } else if (resp.status == 'failed' && !!resp.msg) {
                            el.addClass('alert-' + resp.status).text(resp.msg);
                            form.prepend(el);
                            el.show('slow');
                        } else {
                            el.addClass('alert-' + resp.status).text("An Error occurred.");
                            form.prepend(el);
                            el.show('slow');
                        }
                        form.find('button[type="submit"]').attr('disabled', false);
                    }
                });
            });

            $('#scan-qr-btn').on('click', function() {
                $('#qr-reader').toggle();
                if ($('#qr-reader').is(':visible')) {
                    startQRScanner();
                } else {
                    stopQRScanner();
                }
            });

            var html5QrCode;

            function startQRScanner() {
                html5QrCode = new Html5Qrcode("qr-reader");
                html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 250
                }, function(qrCodeMessage) {
                    try {
                        var qrData = JSON.parse(qrCodeMessage);

                        console.log('Decoded QR Data:', qrData);
                        fetchPatientData('', '', qrData.encrypted_unique_person_id);
                        $('#qr-reader').hide();
                        stopQRScanner();

                        // Make AJAX request to update last scanned datetime
                        $.ajax({
                            url: 'update_last_scanned.php',
                            type: 'POST',
                            data: {
                                encrypted_unique_person_id: qrData.encrypted_unique_person_id
                            },
                            success: function(response) {
                                try {
                                    var result = JSON.parse(response);
                                    if (result.status === 'success') {
                                        console.log('Last scanned datetime updated successfully.');
                                    } else {
                                        console.error('Failed to update last scanned datetime:', result.message);
                                    }
                                } catch (error) {
                                    console.error('Error parsing response JSON:', error, response);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX request failed:', error);
                            }
                        });
                    } catch (error) {
                        console.log('Error parsing QR code message:', error);
                    }
                }, function(errorMessage) {
                    console.log('QR Code Error:', errorMessage);
                });
            }


            function stopQRScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(function(ignore) {
                        html5QrCode.clear();
                    }).catch(function(err) {
                        console.log(err);
                    });
                }
            }

            // Call fetchActiveCashiers function on page load
            fetchActiveCashiers();
        });
    </script>




</body>

</html>