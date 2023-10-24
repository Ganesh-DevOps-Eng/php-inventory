<?php 
require_once 'php_action/db_connect.php';
session_start();

// Check if the user is already logged in and has an active session
if (isset($_SESSION['userId'])) {
    header('location: ' . $store_url . 'dashboard.php');
    exit; // Add an exit to stop script execution after redirection
}

$errors = array();

// Check if the user is trying to log in via SSO
if (isset($_SESSION['sso_login']) && $_SESSION['sso_login'] === true) {
    header('location: ' . $store_url . 'dashboard.php');
    exit; // Add an exit to stop script execution after redirection
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') { // Use $_SERVER['REQUEST_METHOD'] for more robust form handling
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        if (empty($username)) {
            $errors[] = "Username is required";
        } 

        if (empty($password)) {
            $errors[] = "Password is required";
        }
    } else {
        // Validate user credentials and set the session if authentication succeeds
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $connect->query($sql);

        if ($result->num_rows == 1) {
            $password = md5($password); // Make sure this matches your password hashing method
            // Check user credentials
            $mainSql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
            $mainResult = $connect->query($mainSql);

            if ($mainResult->num_rows == 1) {
                $value = $mainResult->fetch_assoc();
                $user_id = $value['user_id'];

                // Set session for the authenticated user
                $_SESSION['userId'] = $user_id;

                header('location: ' . $store_url . 'dashboard.php');
                exit; // Add an exit to stop script execution after successful login
            } else {
                $errors[] = "Incorrect username/password combination";
            }
        } else {		
            $errors[] = "Username does not exist";		
        }
    }
}
?>

<!DOCTYPE html>
<!-- The rest of your HTML code remains unchanged -->
