<?php
if (!is_dir(__DIR__ . '/db')) {
    mkdir(__DIR__ . '/db', 0777, true); // Ensure directory exists with correct permissions
}

// Define absolute path for database file
if (!defined('db_file')) {
    define('db_file', __DIR__ . '/db/cashier_queuing_db.db');
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
    
    function __construct()
    {
        // Open the database connection
        $this->open(db_file);

        // // Set permissions for the database file
        // chmod(db_file, 0666);

        // Register custom MD5 function
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");

        // Create tables if they don't exist
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
            `customer_name` Text NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 0,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert default user if not exists
        $this->exec("INSERT or IGNORE INTO `user_list` VALUES (1,'Administrator','admin',md5('admin123'),1, CURRENT_TIMESTAMP)");
    }
    
    function __destruct()
    {
        // Close the database connection
        $this->close();
    }
}

// Instantiate the DBConnection class to establish the connection
$conn = new DBConnection();

