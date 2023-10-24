<?php 

session_start();

require_once 'db_connect.php';

// echo $_SESSION['userId'];

if (isset($_SESSION['sso_login']) && $_SESSION['sso_login'] === true) {
    header('Location: dashboard.php');

if(!$_SESSION['userId']) {
	header('location:'.$store_url);	
} 


?>