<?php 

session_start();

require_once 'db_connect.php';

// echo $_SESSION['userId'];

if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
    // Handle the case when 'userId' is not set
    // You may want to provide an error message or use a different URL
    header('location:' . $store_url);
    exit; // Add an exit to stop script execution after redirection
}



?>