<?php
// Start the session
session_start();

// Set the session timeout duration in seconds
$session_timeout = 1800; // 30 minutes

// Check if the user is logged in
if (!isset($_SESSION['cashier_id']) || (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout)) {
    // If not logged in or session expired, redirect to login page
    header("Location:./login.php");
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Require necessary files
require_once('./../DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucwords(str_replace('_',' ',$page)) ?> | Patient Queuing System - Doctor View</title>
    <link rel="stylesheet" href="./../Font-Awesome-master/css/all.min.css">
    <link rel="stylesheet" href="./../css/bootstrap.min.css">
    <link rel="stylesheet" href="./../select2/css/select2.min.css">
    <script src="./../js/jquery-3.6.0.min.js"></script>
    <script src="./../js/popper.min.js"></script>
    <script src="./../js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./../DataTables/datatables.min.css">
    <script src="./../DataTables/datatables.min.js"></script>
    <script src="./../Font-Awesome-master/js/all.min.js"></script>
    <script src="./../select2/js/select2.min.js"></script>
    <script src="./../js/script.js"></script>
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

        html,
        body {
            height: 100%;
            background: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        #topNavBar {
            background: var(--primary-color);
            color: #fff;
        }

        #page-container {
            flex: 1;
            padding: 20px;
        }

        .navbar-brand {
            font-weight: 600;
            color: #fff;
        }

        .navbar-dark .navbar-nav .nav-link {
            color: #fff;
        }

        .dynamic_alert {
            margin-top: 20px;
        }

        .modal-dialog.large {
            width: 80% !important;
            max-width: unset;
        }

        .modal-dialog.mid-large {
            width: 50% !important;
            max-width: unset;
        }

        .card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 720px) {
            .modal-dialog.large,
            .modal-dialog.mid-large {
                width: 100% !important;
                max-width: unset;
            }
        }

        .truncate-1,
        .truncate-3 {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }

        .truncate-1 {
            -webkit-line-clamp: 1;
        }

        .truncate-3 {
            -webkit-line-clamp: 3;
        }

        .thumbnail-img,
        .display-select-image {
            width: 60px;
            height: 60px;
            margin: 2px;
        }

        .img-del-btn>.btn {
            font-size: 10px;
            padding: 0 2px !important;
        }

        .img-del-btn {
            right: 2px;
            top: -3px;
        }

        img.display-image {
            width: 100%;
            height: 45vh;
            object-fit: cover;
            background: black;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .navbar-brand,
        .dropdown-menu a {
            font-size: 1.2rem;
        }

        .btn {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: var(--border-radius);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .modal-content {
            border-radius: var(--border-radius);
        }

        .modal-header,
        .modal-footer {
            border: none;
        }

        .modal-title {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <main>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
            <div class="container">
                <a class="navbar-brand" href="./">Doctor View</a>
                <div>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle bg-transparent text-light border-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $_SESSION['name'] ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="./../Actions.php?a=c_logout">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        <div class="container py-3" id="page-container">
            <?php 
                if(isset($_SESSION['flashdata'])):
            ?>
            <div class="dynamic_alert alert alert-<?php echo $_SESSION['flashdata']['type'] ?>">
                <div class="float-end"><a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="$(this).closest('.dynamic_alert').hide('slow').remove()">x</a></div>
                <?php echo $_SESSION['flashdata']['msg'] ?>
            </div>
            <?php unset($_SESSION['flashdata']) ?>
            <?php endif; ?>
            <?php
                $details = $conn->getPatientDetails($_GET['queue_id']);
                if ($details):
            ?>

            <?php endif; ?>
            <?php
                include $page.'.php';
            ?>
        </div>
    </main>
    <div class="modal fade" id="uni_modal" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-sm rounded-0 btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uni_modal_secondary" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal_secondary form').submit()">Save</button>
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
</body>
</html>
