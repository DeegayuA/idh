<?php
session_start();
require_once('DBConnection.php');
require_once('config.php');

class Actions extends DBConnection
{
    private $encryption_key;
    function __construct()
    {
        parent::__construct();
        global $encryption_key;
        $this->encryption_key = base64_decode($encryption_key);
    }


    function __destruct()
    {
        parent::__destruct();
    }

    public function encrypt_data($data)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryption_key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            return false; // Failed to encrypt
        }

        return base64_encode($iv . $encrypted);
    }


    public function decrypt_data($data)
    {
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

    function d_login()
    {
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

    function d_logout()
    {
        session_destroy();
        header("location:./doctorlist/login.php");
    }


    function login()
    {
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
    function logout()
    {
        session_destroy();
        header("location:./login.php");
    }
    function c_login()
    {
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
    function c_logout()
    {
        session_destroy();
        $this->query("UPDATE `cashier_list` set log_status = 0 where cashier_id  = {$_SESSION['cashier_id']}");
        header("location:./cashier");
    }

    function save_user()
    {
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

    public function get_active_cashiers()
    {
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




    function delete_user()
    {
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
    function update_credentials()
    {
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
    function save_cashier()
    {
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
    function delete_cashier()
    {
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
    function save_queue()
    {
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
        $encrypted_unique_person_id = $this->encrypt_data($_POST['encrypted_unique_person_id']);
    
        $preferred_doctor = isset($_POST['preferred_doctor']) ? $_POST['preferred_doctor'] : NULL;
    
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
    
    

    function get_queue()
    {
        extract($_POST);
        $qry = $this->query("SELECT * FROM `queue_list` where queue_id = '{$qid}' ");
        @$res = $qry->fetchArray();
        $resp['status'] = 'success';
        if ($res) {
            $resp['queue'] = $res['queue'];
            $resp['name'] = $this->decrypt_data($res['customer_name']);
            $resp['phone_number'] = $this->decrypt_data($res['phone_number']); // Decrypt phone_number field
        } else {
            $resp['queue'] = "";
            $resp['name'] = "";
            $resp['phone_number'] = "";
        }
        return json_encode($resp);
    }



    function next_queue()
    {
        extract($_POST);
        $get = $this->query("SELECT queue_id, queue, customer_name, age, sex FROM queue_list WHERE status = 0 AND date(date_created) = '" . date("Y-m-d") . "' ORDER BY queue_id ASC LIMIT 1");
        @$res = $get->fetchArray();
        $resp['status'] = 'success';
        if ($res) {
            $this->query("UPDATE queue_list SET status = 1 WHERE queue_id = '{$res['queue_id']}'");
            // Decrypt customer data before returning
            $res['customer_name'] = $this->decrypt_data($res['customer_name']);
            $resp['data'] = $res;
        } else {
            $resp['data'] = $res;
        }
        return json_encode($resp);
    }


    function update_video()
    {
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
