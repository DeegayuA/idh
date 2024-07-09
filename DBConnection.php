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
        $this->openEncryptedDB(DB_FILE, 'DeE@2oo1');
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");
        $this->exec("PRAGMA cache_size = -10000;");
        $this->exec("PRAGMA journal_mode = WAL;");

        $this->createFunction('decrypt_data', [$this, 'decrypt_data']);


        $this->exec("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` TEXT NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->exec("CREATE TABLE IF NOT EXISTS `doctor_list` (
            `doctor_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` TEXT NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `log_status` INTEGER NOT NULL DEFAULT 0,
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
            `phone_number` TEXT,
            `encrypted_id_number` TEXT,
            `encrypted_unique_person_id` TEXT,
            `preferred_doctor` INTEGER DEFAULT NULL
        )");

        $this->exec("CREATE TABLE IF NOT EXISTS `qrcode_list` (
            `qr_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `encrypted_unique_person_id` TEXT NOT NULL,
            `file_name` TEXT NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `last_scanned_datetime` TIMESTAMP
        )");
        

        // Add indexes in the constructor of DBConnection class
        $this->exec("CREATE INDEX IF NOT EXISTS idx_encrypted_id_number ON `queue_list` (`encrypted_id_number`);");
        $this->exec("CREATE INDEX IF NOT EXISTS idx_phone_number ON `queue_list` (`phone_number`);");
        $this->exec("CREATE INDEX IF NOT EXISTS idx_preferred_doctor ON `queue_list` (`preferred_doctor`);");


        $this->exec("INSERT or IGNORE INTO `user_list` VALUES (1,'Administrator','admin',md5('admin123'),1, CURRENT_TIMESTAMP)");

        $this->exec("INSERT or IGNORE INTO `doctor_list` VALUES (1,'Doctor','doc',md5('doc123'),0,1, CURRENT_TIMESTAMP)");
        // doctor_Rooms_#
        $startRoom = 1;
        $totalRooms = 5;

        // Loop through the rooms and insert records
        for ($i = 0; $i < $totalRooms; $i++) {
            $roomNumber = $startRoom + $i;
            $roomName = 'Room' . ($i + 1); // Assuming room names follow this pattern
            // Insert the record into the cashier_list table
            $this->exec("INSERT or IGNORE INTO `cashier_list` VALUES ($roomNumber, '$roomName', 0, 1)");
        }
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

    public function getPatientHistory($identifier, $limit)
    {
        $chunkSize = 500;
        $offset = 0;
        $decrypted_identifiers = [];
        
        do {
            // Step 1: Decrypt phone numbers and NIC numbers in chunks
            $result = $this->query("SELECT queue_id, phone_number, encrypted_id_number 
                                FROM (SELECT queue_id, phone_number, encrypted_id_number 
                                      FROM `queue_list` 
                                      ORDER BY _rowid_ DESC) 
                                LIMIT $chunkSize OFFSET $offset");
    
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $decrypted_phone_number = $this->decrypt_data($row['phone_number']);
                $decrypted_id_number = $this->decrypt_data($row['encrypted_id_number']);
    
                if ($decrypted_phone_number !== false) {
                    $decrypted_identifiers[$row['queue_id']]['phone_number'] = $decrypted_phone_number;
                }
    
                if ($decrypted_id_number !== false) {
                    $decrypted_identifiers[$row['queue_id']]['id_number'] = $decrypted_id_number;
                }
            }
    
            // Step 2: Find exact matches of the entered identifier
            $exact_matches = [];
            foreach ($decrypted_identifiers as $id => $decrypted_data) {
                if (in_array($identifier, $decrypted_data)) {
                    $exact_matches[$id] = $decrypted_data;
                }
            }
    
            if (!empty($exact_matches)) {
                break;
            }
    
            $offset += $chunkSize;
        } while ($result->numColumns() > 0);
    
        // Step 3: Collect all matching entries
        $all_rows = [];
        foreach ($exact_matches as $id => $match_data) {
            $result = $this->query("SELECT * FROM `queue_list` WHERE `queue_id` = '{$id}' ORDER BY `date_created` DESC");
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $row['customer_name'] = $this->decrypt_data($row['customer_name']);
                $row['phone_number'] = $this->decrypt_data($row['phone_number']);
                $row['encrypted_id_number'] = $this->decrypt_data($row['encrypted_id_number']);
    
                $all_rows[] = $row;
            }
        }
    
        // Step 4: Sort the collected rows by date_created in descending order
        usort($all_rows, function ($a, $b) {
            return strtotime($b['date_created']) - strtotime($a['date_created']);
        });
    
        // Check if there are more than one entry
        if (count($all_rows) > 1) {
            // Step 5: Remove the most recent entry
            array_shift($all_rows);
        }
    
        // Step 6: Limit the results to the specified number of entries
        $rows = array_slice($all_rows, 0, $limit);
    
        // Step 7: Destroy the JSON containing decrypted identifiers
        unset($decrypted_identifiers);
    
        return $rows;
    }
    
    public function getPatientDataByUniquePersonID($unique_person_id)
    {
        $chunkSize = 500;
        $offset = 0;
        $all_rows = [];
    
        do {
            // Step 1: Fetch data in chunks
            $result = $this->query("SELECT * FROM `queue_list` WHERE `encrypted_unique_person_id` = '{$unique_person_id}' ORDER BY `date_created` DESC LIMIT $chunkSize OFFSET $offset");
    
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                // Decrypt encrypted fields
                $row['customer_name'] = $this->decrypt_data($row['customer_name']);
                $row['phone_number'] = $this->decrypt_data($row['phone_number']);
                $row['encrypted_id_number'] = $this->decrypt_data($row['encrypted_id_number']);
                $all_rows[] = $row;
            }
    
            if ($result->numColumns() == 0) {
                break;
            }
    
            $offset += $chunkSize;
        } while (true);
    
        // Step 2: Sort the collected rows by date_created in descending order
        usort($all_rows, function ($a, $b) {
            return strtotime($b['date_created']) - strtotime($a['date_created']);
        });
    
        return $all_rows;
    }
    
    public function searchByQRCodeFileName($encrypted_unique_person_id)
    {
        // Step 1: Decrypt last 6 month encrypted_unique_person_id values from the database and store them in an array
        $decryptedIds = [];
        $result = $this->query("SELECT encrypted_unique_person_id FROM qrcode_list ORDER BY date_created ASC LIMIT 180000");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $decryptedId = $this->decrypt_data($row['encrypted_unique_person_id']);

            if ($decryptedId !== false) {
                $decryptedIds[] = $decryptedId;
            }
        }

        // Step 2: Decrypt the provided encrypted_unique_person_id parameter
        $decrypted_param = $this->decrypt_data($encrypted_unique_person_id);

        // Step 3: Find the first matching decrypted parameter
        $matchingId = null;
        foreach ($decryptedIds as $id) {
            if ($id === $decrypted_param) {
                $matchingId = $id;
                echo $matchingId;
                break;
            }
        }

        // Step 4: If a matching ID is found, retrieve the corresponding row from the database
        if ($matchingId !== null) {
            $sql = "SELECT file_name FROM qrcode_list WHERE encrypted_unique_person_id = :encrypted_unique_person_id ORDER BY date_created ASC LIMIT 1";
            $stmt = $this->prepare($sql);
            $stmt->bindValue(':encrypted_unique_person_id', $encrypted_unique_person_id, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            if ($row !== false) {
                // Debugging: Output the retrieved file name
                echo "Retrieved File Name: " . $row['file_name'];
                return $row['file_name'];
            }
        }

        // Step 5: If no matching row was found, return null
        // Debugging: Output a message indicating no matching row was found
        echo "No matching row found for encrypted_unique_person_id: " . $encrypted_unique_person_id;
        return null;
    }

    


    public function fillQRCodeList($encrypted_unique_person_id, $qrFileName)
    {
        // Get the current date and time
        $currentDateTime = date("Y-m-d H:i:s");

        // Insert data into the qrcode_list table
        $sql = "INSERT INTO `qrcode_list` (`encrypted_unique_person_id`, `file_name`, `date_created`) 
                VALUES ('$encrypted_unique_person_id', '$qrFileName', '$currentDateTime')";

        $result = $this->exec($sql);

        // Check if the insertion was successful
        if ($result) {
            return true; // Success
        } else {
            return false; // Failed to insert
        }
    }


    public function checkDecryptedUniquePersonIDExists($encrypted_unique_person_id)
    {
        // Decrypt the provided encrypted_unique_person_id
        $decrypted_unique_person_id = $this->decrypt_data($encrypted_unique_person_id);

        // Prepare the SQL statement to check if the decrypted_unique_person_id exists in the qrcode_list
        $sql = "SELECT COUNT(*) AS count FROM `qrcode_list` WHERE `encrypted_unique_person_id` = :decrypted_unique_person_id";

        // Prepare the statement
        $stmt = $this->prepare($sql);
        $stmt->bindValue(':decrypted_unique_person_id', $decrypted_unique_person_id, SQLITE3_TEXT);

        // Execute the statement
        $result = $stmt->execute();

        // Fetch the row
        $row = $result->fetchArray(SQLITE3_ASSOC);

        // Check if a matching row was found
        if ($row['count'] > 0) {
            return true; // Return true if the decrypted_unique_person_id exists
        } else {
            return false; // Return false if the decrypted_unique_person_id does not exist
        }
    }



    public function updateLastScannedDatetime($encrypted_unique_person_id)
    {

        // Get the current date and time with the specified timezone
        $timezone = new DateTimeZone(tZone); // Use the defined timezone
        $currentDateTime = new DateTime('now', $timezone);
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

        // Prepare the SQL statement
        $sql = "UPDATE qrcode_list SET last_scanned_datetime = :currentDateTime WHERE encrypted_unique_person_id = :encrypted_unique_person_id";

        // Prepare the statement
        $stmt = $this->prepare($sql);

        // Bind parameters
        $stmt->bindValue(':currentDateTime', $formattedDateTime, SQLITE3_TEXT);
        $stmt->bindValue(':encrypted_unique_person_id', $encrypted_unique_person_id, SQLITE3_TEXT);

        // Execute the statement
        $result = $stmt->execute();

        // Check if the update was successful
        if ($result) {
            return true; // Success
        } else {
            return false; // Failed to update
        }
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
