<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database_name = "bhojon28";

// Get connection object and set the charset
$conn = mysqli_connect($host, $username, $password, $database_name);
$conn->set_charset("utf8");

// Get All Table Names From the Database
$tables = array();
$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$sqlScript = "";

// Loop through each table in the database
foreach ($tables as $table) {
    // Fetch the CREATE TABLE query
    $query = "SHOW CREATE TABLE $table";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_row($result);

    // $row[0] contains the table name, $row[1] contains the CREATE TABLE query
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";

    // Fetch data from the table
    $query = "SELECT * FROM $table";
    $result = mysqli_query($conn, $query);
    
    $columnCount = mysqli_num_fields($result);

    while ($row = mysqli_fetch_assoc($result)) {
        $sqlScript .= "INSERT INTO $table (";

        // Extract column names
        $columnNames = array_keys($row);
        $sqlScript .= implode(', ', $columnNames);
        $sqlScript .= ") VALUES (";

        // Escape and quote each value, handling double quotes
        $values = array();
        foreach ($row as $value) {
            $escapedValue = mysqli_real_escape_string($conn, $value);
            $values[] = '"' . $escapedValue . '"';
        }
        $sqlScript .= implode(', ', $values);

        $sqlScript .= ");\n";
    }

    $sqlScript .= "\n";
}

if (!empty($sqlScript)) {
    // Directory path to save the backup file
    $backup_directory = 'backup/';

    // Backup file with the directory path and current date in the file name
    $backup_file_name = $backup_directory . $database_name . '_backup_' . date('Y-m-d') . '.sql';

    // Calculate the date for the previous backup (30 days ago)
    $oneDayAgo = date('Y-m-d', strtotime('-30 days'));
    $Previous_backup_file_name = $backup_directory . $database_name . '_backup_' . $oneDayAgo . '.sql';

    // Open the backup file for writing
    $fileHandler = fopen($backup_file_name, 'w+');

    if ($fileHandler === false) {
        die('Failed to open the backup file for writing.');
    }

    // Write the SQL script to the backup file
    $number_of_lines = fwrite($fileHandler, $sqlScript);

    if ($number_of_lines === false) {
        die('Failed to write data to the backup file.');
    }

    // Delete the previous backup file (if it exists)
    if (file_exists($Previous_backup_file_name)) {
        unlink($Previous_backup_file_name);
    } 

    // Close the backup file
    fclose($fileHandler);

    echo 'Backup successfully created and saved as ' . $backup_file_name;
} else {
    echo 'No data to backup.';
}
?>
