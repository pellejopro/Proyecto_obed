<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "sitio_web_2";
$port = 3307; 

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>