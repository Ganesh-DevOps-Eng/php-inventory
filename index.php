<?php 
require_once 'php_action/db_connect.php';

session_start();

// Check if SSO login is requested
if (isset($_GET['sso']) && $_GET['sso'] == 'true') {
    require_once 'vendor/autoload.php'; // Include the OneLogin toolkit

    $azureADSettings = array(
        'strict' => true,
        'sp' => array(
            'entityId' => 'https://matelliocorp-eb-env.eba-xpnbqghi.us-east-1.elasticbeanstalk.com/',
            'assertionConsumerService' => array(
                'url' => 'https://matelliocorp-eb-env.eba-xpnbqghi.us-east-1.elasticbeanstalk.com/dashboard.php',
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
        $_SESSION['sso_login'] = true;

        // Redirect to the successful SSO login page
        header('Location: dashboard.php');
    }
} elseif (isset($_SESSION['userId'])) {
    // User is already logged in
    header('Location: dashboard.php');
} else {
    $errors = array();

    if ($_POST) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            if (empty($username)) {
                $errors[] = "Username is required";
            } 

            if (empty($password)) {
                $errors[] = "Password is required";
            }
        } else {
            $sql = "SELECT * FROM users WHERE username = '$username'";
            $result = $connect->query($sql);

            if ($result->num_rows == 1) {
                $password = md5($password); // Note: Consider using more secure password hashing methods
                $mainSql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
                $mainResult = $connect->query($mainSql);

                if ($mainResult->num_rows == 1) {
                    $value = $mainResult->fetch_assoc();
                    $user_id = $value['user_id'];

                    // Set session
                    $_SESSION['userId'] = $user_id;

                    header('Location: dashboard.php');
                } else {
                    $errors[] = "Incorrect username/password combination";
                }
            } else {		
                $errors[] = "Username does not exist";		
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Management System</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap theme-->
    <link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="custom/css/custom.css">    

    <!-- jQuery -->
    <script src="assests/jquery/jquery.min.js"></script>
    <!-- jQuery UI -->  
    <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
    <script src="assests/jquery-ui/jquery-ui.min.js"></script>

    <!-- Bootstrap JS -->
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
                                    <label for "password" class="col-sm-2 control-label">Password</label>
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
                        <a href="<?php echo $_SERVER['PHP_SELF'] ?>?sso=true">Login with SSO</a>
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
