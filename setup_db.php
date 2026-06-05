<?php
require_once 'config.php';

try {
    // Connect directly to the database that your hosting provider already created
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents('schema.sql');
    
    // Execute schema
    $conn->exec($sql);
    echo "Tables created successfully in " . DB_NAME . "!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>
<br><br>
<a href="index.php">Go to Homepage</a>
