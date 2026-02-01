<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kickfit_db";

$conn = new mysqli($host, $user, $pass, $db);

/* CHECK CONNECTION PROPERLY */
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* SET UTF-8 FOR PROPER TEXT SUPPORT */
$conn->set_charset("utf8mb4");
?>
