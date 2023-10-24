<?php
require_once 'php_action/db_connect.php';
require_once 'vendor/autoload.php'; // Include the OneLogin toolkit

session_start();

// Define your store_url variable here
$store_url = "your_store_url_here"; // Replace with your actual store URL

$azureADSettings = array(
    'strict' => true,
    'sp' => array(
        'entityId' => 'https://matelliocorp-eb-env.eba-5r5cia6k.us-east-1.elasticbeanstalk.com/', // Your Service Provider Entity ID
        'assertionConsumerService' => array(
            'url' => 'https://matelliocorp-eb-env.eba-5r5cia6k.us-east-1.elasticbeanstalk.com/dashboard.php', // Your application's SSO callback URL
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        'x509cert' => '', // Your SP x509 certificate (leave empty if not used)
        'privateKey' => '', // Your SP private key (leave empty if not used)
    ),
    'idp' => array(
        'entityId' => 'https://sts.windows.net/4c1a87da-93b7-4e87-b5ad-da86a9c2ae72/',
        'singleSignOnService' => array(
            'url' => 'https://login.microsoftonline.com/4c1a87da-93b7-4e87-b5ad-da86a9c2ae72/saml2',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'x509cert' => '', // IdP x509 certificate (leave empty if not used)
    ),
);

$samlAuth = new OneLogin_Saml2_Auth($azureADSettings);

if (!$samlAuth->isAuthenticated()) {
    // Redirect the user to the IdP for authentication
    $samlAuth->login();
} else {
    // User is authenticated, you can access user information with $samlAuth->getAttributes()
    $username = $samlAuth->getAttributes()['NameID'][0];
    // Store the user's information or perform further actions
    // Redirect the user to your application's home page or other appropriate page
    //header('Location: /your-home-page.php');
    // After successful SSO login
    $_SESSION['sso_login'] = true;

    // Redirect to the successful SSO login page
    header('location:' . $store_url . 'dashboard.php');
    exit; // Add this line to stop script execution
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
<html>
<head>
    <title>Stock Management System</title>
    <!-- bootstrap -->
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
    <!-- bootstrap theme-->
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
    <!-- font awesome -->
    <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">
    <!-- custom css -->
    <link rel="stylesheet" href="custom/css/custom.css">    
    <!-- jquery -->
    <script src="assests/jquery/jquery.min.js"></script>
    <!-- jquery ui -->  
    <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
    <script src="assests/jquery-ui/jquery-ui.min.js"></script>
    <!-- bootstrap js -->
    <script src="assests/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="row vertical">
            <div class="col-md-5 col-md-offset-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign in</h3>
                    </div>
                    <div class="panel-body">
                        <div class="messages">
                            <?php if ($errors) {
                                foreach ($errors as $key => $value) {
                                    echo '<div class="alert alert-warning" role="alert">
                                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                                    ' . $value . '</div>';										
                                    }
                                } ?>
                        </div>
                        <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="loginForm">
                            <fieldset>
                              <div class="form-group">
                                    <label for="username" class="col-sm-2 control-label">Username</label>
                                    <div class="col-sm-10">
                                      <input type="text" class="form-control" id="username" name="username" placeholder="Username" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="col-sm-2 control-label">Password</label>
                                    <div class="col-sm-10">
                                      <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off" />
                                    </div>
                                </div>                                
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                      <button type="submit" class="btn btn-default"> <i class="glyphicon glyphicon-log-in"></i> Sign in</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                        <!-- SSO Login Button -->
                        <a href="<?php echo $_SERVER['PHP_SELF'] ?>?sso_login=true">Login with SSO</a>
                    </div>
                    <!-- panel-body -->
                </div>
                <!-- /panel -->
            </div>
            <!-- /col-md-4 -->
        </div>
        <!-- /row -->
    </div>
    <!-- container -->    
</body>
</html>