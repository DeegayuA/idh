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
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/script.js"></script>
    <style>
        :root {
            --background-color: #ffffff;
            --text-color: #000000;
            --card-background: #e8e9ea;
            --primary-color: #4b79a1;
            --primary-hover: #283e51;
            --border-radius: 10px;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --background-color: #121212;
                --text-color: #ffffff;
                --card-background: #272727;
                --primary-color: #4b79a1;
                --primary-hover: #283e51;
            }
        }

        html,
        body {
            height: 100%;
            background: var(--background-color);
            color: var(--text-color);
            font-family: Arial, sans-serif;
        }

        .card {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background: var(--card-background);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: var(--border-radius);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .form-control {
            border-radius: var(--border-radius);
        }

        .h-100 {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header-text {
            font-weight: bold;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .sub-header-text {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .footer-logos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }

        .footer-logos img {
            width: 100px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="h-100">
        <div class='w-100'>
            <h1 class="header-text">National Institute of Infectious Diseases</h1>
            <h2 class="sub-header-text">Patient Queuing System</h2>
            <div class="card my-3 col-md-4 offset-md-4">
                <div class="card-body">
                    <form action="javascript:void(0)" id="login-form">
                        <p class="text-center">Please enter your credentials.</p>
                        <div class="form-group mt-3">
                            <label for="username" class="control-label">Username</label>
                            <input type="text" id="username" autofocus name="username" class="form-control form-control-sm rounded-0" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="password" class="control-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control form-control-sm rounded-0" required>
                        </div>
                        <div class="form-group d-flex w-100 justify-content-between align-items-center mt-3">
                            <span>
                                <a href="./queue_registration.php" class="me-1">Home</a>
                                |
                                <a href="./cashier" class="ms-1">Cashier</a>
                            </span>
                            <button class="btn btn-sm btn-primary rounded-0 my-1">Login</button>
                        </div>
                    </form>
                    <div class="footer-logos">
                        <img src="./logos/EDICWebLogo.png" alt="Logo 1">
                        <span>Created by</span>
                        <img src="./logos/university-of-kelaniya-logo.png" alt="Logo 2">
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
