<?php

$server = "localhost";
$user = "root";  // this is the user in the database, not in your system (in XAMPP, it's "root")
$password = ""; // in XAMPP, "" (there is no password)
$database= "car_info";

// create the connection
$conn = new mysqli($server, $user, $password, $database);

// if connecting failed, abort and show an error message
if ($conn->connect_error) {
   die("Connecting to database failed: " . $conn->connect_error);
}

// set character set to UTF-8
$conn->set_charset("utf8");

?>