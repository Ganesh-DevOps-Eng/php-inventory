<!DOCTYPE html>
<html>
<head>
    <title>PHP Login System</title>
</head>
<body>
    <h1>PHP Login System</h1>

    <?php
session_start();

if (isset($_SESSION['user_id'])) {
    // User is already authenticated via the regular login system
    echo "Welcome, " . $_SESSION['user_id'] . "!<br>";
    echo '<a href="logout.php">Logout</a>';
} elseif (isset($_SESSION['sso_login']) && $_SESSION['sso_login'] === true) {
    // User has successfully logged in via SSO
    header('Location: successful-sso-login.php');
} else {
    if (isset($_POST['login'])) {
        // Handle regular login here
        $username = $_POST['username'];
        $password = $_POST['password'];
        // Your authentication logic here
        if ($validUser) {
            $_SESSION['user_id'] = $username;
            echo "Login successful. Welcome, " . $_SESSION['user_id'] . "!<br>";
            echo '<a href="logout.php">Logout</a>';
        } else {
            echo "Invalid username or password. Please try again.<br>";
        }
    }
}
?>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <input type="submit" name="login" value="Login">
    </form>

    <br>

    <!-- SSO Login Button (Sample) -->
    <a href="sso-login.php">Login with SSO</a>
</body>
</html>