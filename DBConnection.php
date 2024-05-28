<?php
if (!is_dir(__DIR__ . '/db')) {
    mkdir(__DIR__ . '/db', 0777, true); 
}

if (!defined('db_file')) {
    define('db_file', __DIR__ . '/db/cashier_queuing_db.db');
}

if (!defined('tZone')) define('tZone', "Asia/Colombo");
if (!defined('dZone')) define('dZone', ini_get('date.timezone'));

function my_udf_md5($string) {
    return md5($string);
}

class DBConnection extends SQLite3 {
    protected $db;

    function __construct() {
        $this->open(db_file);
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");

        $this->exec("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
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

    function addColumnIfNotExists($table, $column, $type) {
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

    function getPatientHistory($phoneNumber) {
        $sql = "SELECT * FROM `queue_list` WHERE `phone_number` = '$phoneNumber' ORDER BY `date_created` DESC";
        $result = $this->query($sql);

        $history = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $history[] = $row;
        }
        return $history;
    }

    function generateQueueNumber() {
        $date = date("Ymd");
        $time = date("His");
        $unique = substr(uniqid(), -5); // Get the last 5 characters for uniqueness
        return $date . $time . $unique;
    }

    function __destruct() {
        $this->close();
    }
}

$conn = new DBConnection();
?>
