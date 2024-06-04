<?php
session_start();
if(isset($_SESSION['cashier_id']) && $_SESSION['cashier_id'] > 0){
    header("Location:./");
    exit;
}
require_once('./../DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN | IDH Queuing System - Doctor Side</title>
    <link rel="stylesheet" href="./../css/bootstrap.min.css">
    <link rel="stylesheet" href="./../select2/css/select2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
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
            max-width: 400px;
            width: 100%;
            padding: 20px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            background: var(--card-background);
        }

        .header-text {
            font-weight: 600;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .sub-header-text {
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
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
            gap: 1rem;
        }

        .login-footer-links a {
            color: var(--primary-color);
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-footer-links a:hover {
            color: var(--primary-hover);
        }
        .card-body{
            background-color: var(--card-background);
        }
        .select2-dropdown,
        .select2-search {
            background-color: var(--card-background);
        }
        .card{
            background-color: var(--card-background);
            border: none;
        }

        .pop_msg {
            display: none;
            margin-top: 10px;
            text-align: center;
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
        .brakeline{
            color: var(--primary-color);
        }

    </style>
</head>
<body>
   <div class="container">
        <h1 class="header-text">National Institute of Infectious Diseases, Sri Lanka</h1>
        <h2 class="sub-header-text">Doctor Login - Patient Queuing System</h2>
        <div class="card">
            <div class="card-body">
                <form action="" id="login-form">
                    <div class="form-group">
                        <label for="cashier_id" class="control-label">Doctor</label>
                        <select name="cashier_id" id="cashier_id" class="custom-select select2">
                            <option disabled selected>Select Doctor</option>
                            <?php 
                            $cashier = $conn->query("SELECT * FROM `cashier_list` where `status` = 1 order by `name` asc");
                            while($row = $cashier->fetchArray()):
                            ?>
                                <option value="<?php echo $row['cashier_id'] ?>"><?php echo $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary">Login</button>
                    </div>
                    <div class="login-footer-links">
                        <a href="./../queue_registration.php">Home</a>
                        <span class="brakeline"> | </span>
                        <a href="./../">Admin</a>
                        <span class="brakeline"> | </span>
                        <a href="./../doctorlist/login.php">Doctor List</a>
                    </div>
                    <div class="pop_msg"></div>
                </form>
            </div>
        </div>
        <div class="footer-logos">
                    <span>Powered by EDIC UOK</span>
                    <div class="logos">
                        <img src=".././logos/EDICWebLogo.png" alt="EDIC Web Logo">
                        <img src=".././logos/university-of-kelaniya-logo.png" alt="University of Kelaniya Logo">
                    </div>
                </div>
   </div>
</body>
<script src="./../js/jquery-3.6.0.min.js"></script>
<script src="./../js/popper.min.js"></script>
<script src="./../js/bootstrap.min.js"></script>
<script src="./../select2/js/select2.min.js"></script>
<script>
    $(function(){
        $('.select2').select2();
        $('#login-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').hide().empty();
            var _this = $(this);
            var _el = $('<div>').addClass('pop_msg');
            _this.find('button').prop('disabled', true).text('Logging in...');
            $.ajax({
                url: './../Actions.php?a=c_login',
                method: 'POST',
                data: _this.serialize(),
                dataType: 'JSON',
                error: function(xhr, status, error) {
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
                    _el.addClass('alert alert-danger').text(errMsg);
                    _this.prepend(_el);
                    _el.slideDown();
                    _this.find('button').prop('disabled', false).text('Login');
                },
                success: function(resp) {
                    if(resp.status == 'success'){
                        location.replace('./');
                    } else {
                        _el.addClass('alert alert-danger').text(resp.msg);
                    }
                    _this.prepend(_el);
                    _el.slideDown();
                    _this.find('button').prop('disabled', false).text('Login');
                }
            });
        });
    });
</script>
</html>
