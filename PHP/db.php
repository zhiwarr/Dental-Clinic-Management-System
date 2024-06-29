<?php
$host = "localhost";
$port = "5432";
$dbname = "DentalClinic";
$user = "postgres";
$password = "zhiwar";

try {
    $dbconn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    // Set PDO to throw exceptions for errors
    $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>