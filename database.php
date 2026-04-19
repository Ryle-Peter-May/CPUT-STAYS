<?php

$host = "sql213.infinityfree.com";
$dbname = "if0_41700525_cput_stays";
$username = "if0_41700525";
$password = "14kszhIVx0WH";

$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);

if($mysqli->connect_errno){
    die("Connection error:" . $mysqli->connect_error);

}

return $mysqli;
