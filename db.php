<?php
$host = "localhost";
$username = "unuw9ry46la8t";
$password = "4cgdhp7dokz1";
$database = "db1qhm7oxehn5f";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
