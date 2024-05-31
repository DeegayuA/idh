<?php
session_start();
require_once('DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucwords(str_replace('_',' ',$page)) ?> | IDH Queuing System</title>
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

        html, body {
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
        .text-info2{
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

        .brakeline{
            color: var(--primary-color);
        }
        .footer-logos img {
            height: 50px;
            width: auto;
            margin: 10px;
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
            if(isset($_SESSION['flashdata'])):
        ?>
        <div class="dynamic_alert alert alert-<?php echo $_SESSION['flashdata']['type'] ?>">
            <div class="float-end"><a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="$(this).closest('.dynamic_alert').hide('slow').remove()">x</a></div>
            <?php echo $_SESSION['flashdata']['msg'] ?>
        </div>
        <?php unset($_SESSION['flashdata']) ?>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
                <div class="h5 card-title">Get your Queue Number Here</div>
                <form action="" id="queue-form">
                <div class="form-group mb-3">
                        <label for="phone_number" class="control-label text-info2">Enter Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="customer_name" class="control-label text-info2">Enter your Name</label>
                        <input type="text" id="customer_name" name="customer_name" autofocus autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="age" class="control-label text-info2">Enter your Age</label>
                        <input type="number" id="age" name="age"  autocomplete="off" class="form-control form-control-lg rounded-0 border-0 border-bottom" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="sex" class="control-label text-info2">Select Sex</label>
                        <select name="sex" id="sex" class="form-select rounded-0 border-0 border-bottom" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

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
        $(function(){
            $('#queue-form').submit(function(e){
                e.preventDefault()
                var _this = $(this)
                _this.find('.pop-msg').remove()
                var el = $('<div>')
                    el.addClass('alert pop-msg')
                    el.hide()
                    _this.find('button[type="submit"]').attr('disabled',true)
                    $.ajax({
                        url:'./Actions.php?a=save_queue',
                        method:'POST',
                        data:_this.serialize(),
                        dataType:'JSON',
                        error:err=>{
                            console.log(err)
                            el.addClass("alert-danger")
                            el.text("An error occured while saving data.")
                            _this.find('button[type="submit"]').attr('disabled',false)
                            _this.prepend(el)
                            el.show('slow')
                        },
                        success:function(resp){
                            if(resp.status == 'success'){
                                uni_modal("Your Queue","get_queue.php?success=true&id="+resp.id)
                                $('#uni_modal').on('hide.bs.modal',function(e){
                                    location.reload()
                                })
                            }else if(resp.status ='failed' && !!resp.msg){
                                el.addClass('alert-'+resp.status)
                                el.text(resp.msg)
                                _this.prepend(el)
                                el.show('slow')
                            }else{
                                el.addClass('alert-'+resp.status)
                                el.text("An Error occured.")
                                _this.prepend(el)
                                el.show('slow')
                            }
                            _this.find('button[type="submit"]').attr('disabled',false)
                        }
                    })
            })
        })
    </script>
</body>
</html>
