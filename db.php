<?php
//masked host configuration

$host = "infinityfree.com"; 
$user = "if9$%@48691";            
$pass = "a087656969";      
$dbname = "if9$%@48691_eventregdb";    

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
