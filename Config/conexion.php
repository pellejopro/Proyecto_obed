<?php
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "sitio_web_2";
$port = 3306; 

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>