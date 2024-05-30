<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    header("Location:./");
    exit;
}
require_once('DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN | IDH Queuing System</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/script.js"></script>
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

        html, body {
            height: 100%;
            background: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            background: var(--card-background);
        }

        .header-text {
            font-weight: 600;
            font-size: 1.75rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .sub-header-text {
            font-size: 1.25rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
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

        .login-footer-links {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 1rem;
            gap: 1rem;
        }
        .brakeline{
            color: var(--primary-color);
        }
        .login-footer-links a {
            color: var(--primary-color);
            font-weight: bolder;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-footer-links a:hover {
            color: var(--primary-hover);
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

        .pop_msg {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="header-text">National Institute of Infectious Diseases <br> Sri Lanka</h1>
        <h2 class="sub-header-text">Patient Queuing System</h2>
        <div class="card2 my-3">
            <div class="card-body">
                <form action="javascript:void(0)" id="login-form">
                    <!-- <p class="text-center">Please enter your credentials.</p> -->
                    <div class="form-group">
                        <label for="username" class="control-label">Username</label>
                        <input type="text" id="username" autofocus name="username" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="control-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg">Login</button>
                    </div>
                    <div class="login-footer-links">
                        <a href="./queue_registration.php">Home</a>
                        <span class="brakeline"> | </span>
                        <a href="./cashier">Doctors</a>
                    </div>
                    <div class="pop_msg"></div>
                </form>
                <div class="footer-logos">
                    <span>Powered by EDIC UOK</span>
                    <div class="logos">
                        <img src="./logos/EDICWebLogo.png" alt="EDIC Web Logo">
                        <img src="./logos/university-of-kelaniya-logo.png" alt="University of Kelaniya Logo">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    $(function() {
        $('#login-form').submit(function(e) {
            e.preventDefault();
            $('.pop_msg').remove();
            var _this = $(this);
            var _el = $('<div>');
            _el.addClass('pop_msg');
            _this.find('button').attr('disabled', true);
            _this.find('button[type="submit"]').text('Logging in...');
            $.ajax({
                url: './Actions.php?a=login',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'JSON',
                error: err => {
                    console.log(err);
                    _el.addClass('alert alert-danger');
                    _el.text("An error occurred.");
                    _this.prepend(_el);
                    _el.show('slow');
                    _this.find('button').attr('disabled', false);
                    _this.find('button[type="submit"]').text('Login');
                },
                success: function(resp) {
                    if (resp.status == 'success') {
                        _el.addClass('alert alert-success');
                        setTimeout(() => {
                            location.replace('./');
                        }, 2000);
                    } else {
                        _el.addClass('alert alert-danger');
                    }
                    _el.text(resp.msg);
                    _el.hide();
                    _this.prepend(_el);
                    _el.show('slow');
                    _this.find('button').attr('disabled', false);
                    _this.find('button[type="submit"]').text('Login');
                }
            });
        });
    });
</script>

</html>
