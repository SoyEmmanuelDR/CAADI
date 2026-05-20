<?php 
    $host = "localhost";      
    $user = "root";        
    $password = "";        
    $database = "caadi"; 
    $port = 3306;
    $conn = new mysqli($host, $user, $password, $database, $port);
    if ($conn->connect_error) 
    { 
        die("Error de conexión: " . $conn->connect_error); 
    } 
    $conn->set_charset("utf8");
?>