<?php
require_once('config.php'); 

if (!is_dir(__DIR__ . '/db')) {
    mkdir(__DIR__ . '/db', 0777, true);
}

if (!defined('DB_FILE')) {
    define('DB_FILE', __DIR__ . '/db/cashier_queuing_db.db');
}

if (!defined('tZone')) define('tZone', "Asia/Colombo");
if (!defined('dZone')) define('dZone', ini_get('date.timezone'));

function my_udf_md5($string)
{
    return md5($string);
}

class DBConnection extends SQLite3
{
    protected $db;
    private $encryption_key;

    function __construct()
    {
        global $encryption_key;
        $this->encryption_key = base64_decode($encryption_key);

        // Check if database file exists, if not, create it
        if (!file_exists(DB_FILE)) {
            touch(DB_FILE); // Create an empty file
        }

        // Open the database with encryption
        $this->openEncryptedDB(DB_FILE, 'my_secret_key');
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");

        $this->exec("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` TEXT NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->exec("CREATE TABLE IF NOT EXISTS `cashier_list` (
            `cashier_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL,
            `log_status` INTEGER NOT NULL DEFAULT 0,
            `status` INTEGER NOT NULL DEFAULT 1
        )");

        $this->exec("CREATE TABLE IF NOT EXISTS `queue_list` (
            `queue_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `queue` TEXT NOT NULL,
            `customer_name` TEXT NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 0,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `age` INTEGER,
            `sex` TEXT,
            `phone_number` TEXT
        )");

        $this->exec("INSERT or IGNORE INTO `user_list` VALUES (1,'Administrator','admin',md5('admin123'),1, CURRENT_TIMESTAMP)");
    }

    private function openEncryptedDB($file, $key)
    {
        $this->open($file);
        $this->exec("PRAGMA key = '$key';");

        // Optionally, verify if the key is correct
        $result = $this->query("SELECT count(*) FROM sqlite_master;");
        if ($result === false) {
            throw new Exception("Failed to open the database with the provided key.");
        }
    }

    function addColumnIfNotExists($table, $column, $type)
    {
        $result = $this->query("PRAGMA table_info($table)");
        $exists = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] == $column) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->exec("ALTER TABLE $table ADD COLUMN $column $type");
        }
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


public function getPatientHistory($phone_number, $limit = null)
{
    $qry = "SELECT * FROM `queue_list` WHERE `phone_number` = '$phone_number' ORDER BY `date_created` DESC";
    if ($limit) {
        $qry .= " LIMIT $limit";
    }
    $result = $this->query($qry);
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Decrypt the encrypted fields before adding them to the result
        $row['customer_name'] = $this->decrypt_data($row['customer_name']);
        $row['phone_number'] = $this->decrypt_data($row['phone_number']);
        $rows[] = $row;
    }
    return $rows;
}


    function generateQueueNumber()
    {
        $date = date("Ymd");
        $time = date("His");
        $unique = substr(uniqid(), -5); // Get the last 5 characters for uniqueness
        return $date . $time . $unique;
    }

    function getPatientDetails($queue_id)
    {
        $sql = "SELECT customer_name, age, sex FROM `queue_list` WHERE `queue_id` = '$queue_id'";
        $result = $this->querySingle($sql, true);
        if ($result) {
            $result['customer_name'] = $this->decrypt_data($result['customer_name']);
        }
        return $result;
    }

    function __destruct()
    {
        $this->close();
    }
}

$conn = new DBConnection();
?>
