<?php

$host = "localhost";
$dbname = "cput_stays";
$username = "root";
$password = "A1z#334me";

$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);

if($mysqli->connect_errno){
    die("Connection error:" . $mysqli->connect_error);

}

return $mysqli;