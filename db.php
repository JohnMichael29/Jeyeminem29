<?php
// connection to the database (default xampp settings)
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "saving_memories";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
