
<?php
// Author: Deeghayu Suwahas Adhikari
// https://deeghayu.netlify.app/

// Function: Class for handling various actions related to the queue management system.
// This class extends DBConnection for database operations.


session_start();
require_once('DBConnection.php');
require_once('config.php');

class Actions extends DBConnection
{
    // Function: Constructor method for Actions class.
    // Initializes the encryption key for data encryption and decryption.
    private $encryption_key;
    function __construct()
    {
        parent::__construct();
        global $encryption_key;
        $this->encryption_key = base64_decode($encryption_key);
    }

    // Function: Destructor method for Actions class.
    // Performs cleanup tasks when the object is destroyed.
    function __destruct()
    {
        parent::__destruct();
    }

    // Function: Encrypts data using AES-256-CBC encryption.
    public function encrypt_data($data)
    {
        // Generates a random initialization vector (IV).
        // Encrypts the data using AES-256-CBC algorithm with the encryption key and IV.
        // Returns the encrypted data in base64-encoded format.
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryption_key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            return false; // Failed to encrypt
        }

        return base64_encode($iv . $encrypted);
    }

    // Function: Decrypts data previously encrypted using AES-256-CBC.
    public function decrypt_data($data)
    {
        // Decodes the base64-encoded data.
        // Retrieves the IV and encrypted data.
        // Decrypts the data using AES-256-CBC algorithm with the encryption key and IV.
        // Returns the decrypted data.
        $data = base64_decode($data);
        if ($data === false) {
            return false; // Failed to decode base64
        }

        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);

        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryption_key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            return false; // Failed to decrypt
        }

        return $decrypted;
    }
    // Function: Retrieves a list of active cashiers from the database.
    public function get_active_cashiers()
    {
        // Constructs SQL query to select active cashiers.
        // Retrieves and returns the list of active cashiers as JSON.
        $sql = "SELECT * FROM `cashier_list` WHERE `status` = 1";
        $qry = $this->query($sql);
        $cashiers = [];
        while ($row = $qry->fetchArray(SQLITE3_ASSOC)) {
            $cashiers[] = $row;
        }
        $resp['status'] = 'success';
        $resp['data'] = $cashiers;
        return json_encode($resp);
    }
    // Function: Handles login functionality for doctors.

    function d_login()
    {
        // Validates doctor's login credentials.
        // Sets session variables upon successful login.
        // Returns login status and message as JSON.
        extract($_POST);
        $sql = "SELECT * FROM doctor_list where username = '{$username}' and `password` = '" . md5($password) . "' ";
        @$qry = $this->query($sql)->fetchArray();
        if (!$qry) {
            $resp['status'] = "failed";
            $resp['msg'] = "Invalid username or password.";
        } else {
            $resp['status'] = "success";
            $resp['msg'] = "Login successfully.";
            foreach ($qry as $k => $v) {
                if (!is_numeric($k))
                    $_SESSION[$k] = $v;
            }
        }
        return json_encode($resp);
    }
// Function: Logs out a doctor by destroying session and redirecting to login page.

    function d_logout()
    {
        // Function: Logs out a doctor by destroying session and redirecting to login page.
        session_destroy();
        header("location:./doctorlist/login.php");
    }

// Function: Handles login functionality for users.
    function login()
    {
        // Validates user's login credentials.
        // Sets session variables upon successful login.
        // Returns login status and message as JSON.
        extract($_POST);
        $sql = "SELECT * FROM user_list where username = '{$username}' and `password` = '" . md5($password) . "' ";
        @$qry = $this->query($sql)->fetchArray();
        if (!$qry) {
            $resp['status'] = "failed";
            $resp['msg'] = "Invalid username or password.";
        } else {
            $resp['status'] = "success";
            $resp['msg'] = "Login successfully.";
            foreach ($qry as $k => $v) {
                if (!is_numeric($k))
                    $_SESSION[$k] = $v;
            }
        }
        return json_encode($resp);
    }
    
    // Function: Logs out a user by destroying session and redirecting to login page.
    function logout()
    {
        // Logs out a user by destroying session and redirecting to login page.
        session_destroy();
        header("location:./login.php");
    }

    // Function: Handles login functionality for cashiers.
    function c_login()
    {
        // Validates cashier's login credentials.
        // Sets session variables upon successful login.
        // Returns login status and message as JSON.

        extract($_POST);
        $sql = "SELECT * FROM cashier_list where cashier_id = '{$cashier_id}'";
        @$qry = $this->query($sql)->fetchArray();
        if ($qry) {
            if ($qry['log_status'] == 0) {
                $resp['status'] = "success";
                $resp['msg'] = "Login successfully.";
                $this->query("UPDATE `cashier_list` set log_status = 1 where cashier_id  = {$cashier_id}");
                foreach ($qry as $k => $v) {
                    if (!is_numeric($k))
                        $_SESSION[$k] = $v;
                }
            } else {
                $resp['failed'] = "failed";
                $resp['msg'] = "Doctor is In-Use.";
            }
        } else {
            $resp['status'] = "success";
            $resp['msg'] = "An Error occured. Error: " . $this->lastErrorMsg();
        }
        return json_encode($resp);
    }

    // Function: Logs out a cashier by destroying session and redirecting to cashier page.
    function c_logout()
    {
        // Logs out a cashier by destroying session and updating log status.
        session_destroy();
        $this->query("UPDATE `cashier_list` set log_status = 0 where cashier_id  = {$_SESSION['cashier_id']}");
        header("location:./cashier");
    }

    // Function: Saves user details to the database.
    function save_user()
    {
       // Processes form data to save or update user details.
    // Validates input and checks for existing usernames.
    // Encrypts password if it's a new user.
    // Returns operation status and message as JSON.
        extract($_POST);
        $data = "";
        $cols = [];
        $values = [];

        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id'))) {
                if (!empty($id)) {
                    if (!empty($data)) $data .= ",";
                    $data .= " `{$k}` = '{$v}' ";
                } else {
                    $cols[] = $k;
                    $values[] = "'{$v}'";
                }
            }
        }

        if (empty($id)) {
            // Ensure fullname is included for new users
            if (!isset($fullname) || empty($fullname)) {
                $resp['status'] = 'failed';
                $resp['msg'] = "Fullname is required.";
                return json_encode($resp);
            }
            // Include password if not set
            if (!in_array('password', $cols)) {
                $cols[] = 'password';
                $values[] = "'" . md5($password) . "'";
            }
        }

        if (isset($cols) && isset($values)) {
            $data = "(" . implode(',', $cols) . ") VALUES (" . implode(',', $values) . ")";
        }

        @$check = $this->query("SELECT count(user_id) as `count` FROM user_list where `username` = '{$username}' " . ($id > 0 ? " and user_id != '{$id}' " : ""))->fetchArray()['count'];
        if (@$check > 0) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Username already exists.";
        } else {
            if (empty($id)) {
                // Ensure fullname is included for new users
                if (!isset($fullname) || empty($fullname)) {
                    $resp['status'] = 'failed';
                    $resp['msg'] = "Fullname is required.";
                    return json_encode($resp);
                }
                // Include password if not set
                if (!in_array('password', $cols)) {
                    $cols[] = 'password';
                    $values[] = "'" . md5($password) . "'";
                }
            }

            if (isset($cols) && isset($values)) {
                $data = "(" . implode(',', $cols) . ") VALUES (" . implode(',', $values) . ")";
            }

            $sql = empty($id) ? "INSERT INTO `user_list` {$data}" : "UPDATE `user_list` set {$data} where user_id = '{$id}'";
            $save = $this->query($sql);

            if ($save) {
                $resp['status'] = 'success';
                if (empty($id))
                    $resp['msg'] = 'New User successfully saved.';
                else
                    $resp['msg'] = 'User Details successfully updated.';
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = 'Saving User Details Failed. Error: ' . $this->lastErrorMsg();
            }
        }
        // Ensure there are no additional statements after this point
        return json_encode($resp);
    }

    // Function: Deletes a user from the database.
    function delete_user()
    {
        // Deletes a user from the database.
        // Returns operation status and message as JSON.
        
        extract($_POST);

        @$delete = $this->query("DELETE FROM `user_list` where rowid = '{$id}'");
        if ($delete) {
            $resp['status'] = 'success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'User successfully deleted.';
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->lastErrorMsg();
        }
        return json_encode($resp);
    }

    // Function: Updates user credentials in the database.
    function update_credentials()
    {
        // Updates user credentials in the database.
        // Validates old password before updating.
        // Returns operation status and message as JSON.
        extract($_POST);
        $data = "";
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id', 'old_password')) && !empty($v)) {
                if (!empty($data)) $data .= ",";
                if ($k == 'password') $v = md5($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        if (!empty($password) && md5($old_password) != $_SESSION['password']) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Old password is incorrect.";
        } else {
            $sql = "UPDATE `user_list` set {$data} where user_id = '{$_SESSION['user_id']}'";
            @$save = $this->query($sql);
            if ($save) {
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Credential successfully updated.';
                foreach ($_POST as $k => $v) {
                    if (!in_array($k, array('id', 'old_password')) && !empty($v)) {
                        if (!empty($data)) $data .= ",";
                        if ($k == 'password') $v = md5($v);
                        $_SESSION[$k] = $v;
                    }
                }
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = 'Updating Credentials Failed. Error: ' . $this->lastErrorMsg();
                $resp['sql'] = $sql;
            }
        }
        return json_encode($resp);
    }

    // Function: Saves cashier details to the database.
    function save_cashier()
    {
        // Processes form data to save or update cashier details.
        // Validates input and checks for existing cashiers.
        // Returns operation status and message as JSON.
        extract($_POST);
        $data = "";
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id'))) {
                $v = addslashes(trim($v));
                if (empty($id)) {
                    $cols[] = "`{$k}`";
                    $vals[] = "'{$v}'";
                } else {
                    if (!empty($data)) $data .= ", ";
                    $data .= " `{$k}` = '{$v}' ";
                }
            }
        }
        if (isset($cols) && isset($vals)) {
            $cols_join = implode(",", $cols);
            $vals_join = implode(",", $vals);
        }
        if (empty($id)) {
            $sql = "INSERT INTO `cashier_list` ({$cols_join}) VALUES ($vals_join)";
        } else {
            $sql = "UPDATE `cashier_list` set {$data} where cashier_id = '{$id}'";
        }
        @$check = $this->query("SELECT COUNT(cashier_id) as count from `cashier_list` where `name` = '{$name}' " . ($id > 0 ? " and cashier_id != '{$id}'" : ""))->fetchArray()['count'];
        if (@$check > 0) {
            $resp['status'] = 'failed';
            $resp['msg'] = 'Cashier already exists.';
        } else {
            @$save = $this->query($sql);
            if ($save) {
                $resp['status'] = "success";
                if (empty($id))
                    $resp['msg'] = "Cashier successfully saved.";
                else
                    $resp['msg'] = "Cashier successfully updated.";
            } else {
                $resp['status'] = "failed";
                if (empty($id))
                    $resp['msg'] = "Saving New Cashier Failed.";
                else
                    $resp['msg'] = "Updating Cashier Failed.";
                $resp['error'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }

    // Function: Deletes a cashier from the database.
    function delete_cashier()
    {
        // Deletes a cashier from the database.
        // Checks if the cashier is in use before deletion.
        // Returns operation status and message as JSON.
        extract($_POST);
        $get = $this->query("SELECT * FROM `cashier_list` where cashier_id = '{$id}'");
        @$res = $get->fetchArray();
        $is_logged = false;
        if ($res) {
            $is_logged = $res['log_status'] == 1 ? true : false;
            if ($is_logged) {
                $resp['status'] = 'failed';
                $resp['msg'] = 'Cashier is in use.';
            } else {
                @$delete = $this->query("DELETE FROM `cashier_list` where cashier_id = '{$id}'");
                if ($delete) {
                    $resp['status'] = 'success';
                    $_SESSION['flashdata']['type'] = 'success';
                    $_SESSION['flashdata']['msg'] = 'Cashier successfully deleted.';
                } else {
                    $resp['status'] = 'failed';
                    $resp['error'] = $this->lastErrorMsg();
                }
            }
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->lastErrorMsg();
        }

        return json_encode($resp);
    }

    // Function: Saves a new queue to the database.
    function save_queue()
    {
       // Generates a unique queue code.
    // Encrypts customer data before saving.
    // Inserts queue details into the database.
    // Returns operation status, message, and newly inserted queue ID as JSON.
        $code = sprintf("%'.04d", 1);
        while (true) {
            $chk = $this->query("SELECT count(queue_id) `count` FROM `queue_list` where queue = '" . $code . "' and date(date_created) = '" . date('Y-m-d') . "' ")->fetchArray()['count'];
            if ($chk > 0) {
                $code = sprintf("%'.04d", abs($code) + 1);
            } else {
                break;
            }
        }

        // Encrypt customer data before saving
        $encrypted_customer_name = $this->encrypt_data($_POST['customer_name']);
        $encrypted_phone_number = $this->encrypt_data($_POST['phone_number']);
        $encrypted_id_number = $this->encrypt_data($_POST['encrypted_id_number']);

        $preferred_doctor = isset($_POST['preferred_doctor']) ? $_POST['preferred_doctor'] : NULL;

        // Generate the encrypted_unique_person_id
        $encrypted_unique_person_id = $this->encrypt_data($_POST['customer_name'] . $_POST['sex'] . $_POST['encrypted_id_number'] . $_POST['phone_number']);

        $sql = "INSERT INTO `queue_list` (`queue`,`customer_name`, `status`, `age`, `sex`, `phone_number`, `encrypted_id_number`, `encrypted_unique_person_id`, `preferred_doctor`) 
                VALUES('{$code}', '{$encrypted_customer_name}', 0, '{$_POST['age']}', '{$_POST['sex']}', '{$encrypted_phone_number}', '{$encrypted_id_number}', '{$encrypted_unique_person_id}', '{$preferred_doctor}')";

        $save = $this->query($sql);
        if ($save) {
            $resp['status'] = 'success';
            $resp['id'] = $this->lastInsertRowID(); // Change this line
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = "An error occurred. Error: " . $this->lastErrorMsg();
        }
        return json_encode($resp);
    }

// Function: Retrieves queue details based on queue ID from the database.
    function get_queue()
    {
         // Retrieves queue details using queue ID.
    // Decrypts customer data before returning.
    // Returns queue details as JSON.
        extract($_POST);
        $qry = $this->query("SELECT * FROM `queue_list` where queue_id = '{$qid}' ");
        @$res = $qry->fetchArray();
        $resp['status'] = 'success';
        if ($res) {
            $resp['queue'] = $res['queue'];
            $resp['name'] = $this->decrypt_data($res['customer_name']);
            $resp['phone_number'] = $this->decrypt_data($res['phone_number']);
        } else {
            $resp['queue'] = "";
            $resp['name'] = "";
            $resp['phone_number'] = "";
        }
        return json_encode($resp);
    }

    // Function: Advances to the next queue and assigns it to a doctor.
    function next_queue()
    {
         // Retrieves the next available queue.
    // Checks if the queue has a preferred doctor or matches current doctor.
    // Updates queue status to indicate it's being serviced.
    // Decrypts customer data before returning.
    // Returns next queue details as JSON.
        extract($_POST);

        // Select the next queue, prioritizing by queue_id but considering preferred doctor
        $get = $this->query("
            SELECT queue_id, queue, customer_name, age, sex, preferred_doctor 
            FROM queue_list 
            WHERE status = 0 AND date(date_created) = '" . date("Y-m-d") . "' 
            ORDER BY queue_id ASC 
            LIMIT 1
        ");

        @$res = $get->fetchArray();
        $resp['status'] = 'success';
        if ($res) {
            if ($res['preferred_doctor'] == 0 || $res['preferred_doctor'] == $doctor_id || is_null($res['preferred_doctor'])) {
                // The next queue item is either for the current doctor or doesn't have a preferred doctor
                $this->query("UPDATE queue_list SET status = 1 WHERE queue_id = '{$res['queue_id']}'");
                // Decrypt customer data before returning
                $res['customer_name'] = $this->decrypt_data($res['customer_name']);
                $resp['data'] = $res;
            } else {
                // If the next item has a different preferred doctor, skip it temporarily and check the next one
                $get_next = $this->query("
                    SELECT queue_id, queue, customer_name, age, sex, preferred_doctor 
                    FROM queue_list 
                    WHERE status = 0 AND date(date_created) = '" . date("Y-m-d") . "' 
                    AND (preferred_doctor IS NULL OR preferred_doctor = 0 OR preferred_doctor = '$doctor_id') 
                    ORDER BY queue_id ASC 
                    LIMIT 1
                ");

                @$res_next = $get_next->fetchArray();
                if ($res_next) {
                    $this->query("UPDATE queue_list SET status = 1 WHERE queue_id = '{$res_next['queue_id']}'");
                    // Decrypt customer data before returning
                    $res_next['customer_name'] = $this->decrypt_data($res_next['customer_name']);
                    $resp['data'] = $res_next;
                } else {
                    // No available queue for the current doctor
                    $resp['data'] = null;
                }
            }
        } else {
            $resp['data'] = null;
        }
        return json_encode($resp);
    }

    // Function: Updates the video file in the system.
    function update_video()
    {
         // Handles file upload for updating video content.
    // Validates file upload and MIME type.
    // Moves uploaded file to designated directory.
    // Removes old video file if exists.
    // Sets flash data for success or failure message.
    // Returns operation status and message as JSON.
        extract($_FILES);

        // Check for file upload errors
        if ($vid['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = array(
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
            );

            if (isset($uploadErrors[$vid['error']])) {
                $resp['status'] = 'false';
                $resp['msg'] = 'File upload error: ' . $uploadErrors[$vid['error']];
            } else {
                $resp['status'] = 'false';
                $resp['msg'] = 'Unknown upload error.';
            }
            return json_encode($resp);
        }

        // Check file MIME type
        $mime = mime_content_type($vid['tmp_name']);
        if (!strstr($mime, 'video/')) {
            $resp['status'] = 'false';
            $resp['msg'] = 'Invalid video type.';
            return json_encode($resp);
        }

        // Move uploaded file to destination
        $uploadDir = "./video/";
        $uploadFile = $uploadDir . time() . "_" . basename($vid['name']);

        if (move_uploaded_file($vid['tmp_name'], $uploadFile)) {
            // File uploaded successfully
            $resp['status'] = 'success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Video was successfully updated.';

            // Remove old video file if exists
            if (is_file('./video/' . $_POST['video'])) {
                unlink('./video/' . $_POST['video']);
            }
        } else {
            // Failed to move the uploaded file
            $resp['status'] = 'false';
            $resp['msg'] = 'Unable to upload the video.';
        }

        return json_encode($resp);
    }
}
// Switch statement to execute specific actions based on 'a' parameter.
// Executes corresponding method based on the value of 'a'.
$a = isset($_GET['a']) ? $_GET['a'] : '';
$action = new Actions();
switch ($a) {
    case 'login':
        echo $action->login();
        break;
    case 'c_login':
        echo $action->c_login();
        break;
    case 'd_login':
        echo $action->d_login();
        break;
    case 'logout':
        echo $action->logout();
        break;
    case 'c_logout':
        echo $action->c_logout();
        break;
    case 'd_logout':
        echo $action->d_logout();
        break;
    case 'save_user':
        echo $action->save_user();
        break;
    case 'delete_user':
        echo $action->delete_user();
        break;
    case 'update_credentials':
        echo $action->update_credentials();
        break;
    case 'save_cashier':
        echo $action->save_cashier();
        break;
    case 'delete_cashier':
        echo $action->delete_cashier();
        break;
    case 'save_queue':
        echo $action->save_queue();
        break;
    case 'get_queue':
        echo $action->get_queue();
        break;
    case 'next_queue':
        echo $action->next_queue();
        break;
    case 'update_video':
        echo $action->update_video();
        break;
    case 'get_active_cashiers':
        echo $action->get_active_cashiers();
        break;

    default:
        // default action here
        break;
}
