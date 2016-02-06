<?php
require_once ('includes/common/require.php');

//get referer page
$redirect = get_referer_url();

// Store the messages in a variable to prevent interfering with headers manipulation.
$msg = '';

// This is the action requested by the user
$action = @$_POST['action'];

// This is the first uLogin-specific line in this file.
// We construct an instance and pass a function handle to our
// callback functions (we have just defined 'appLogin' and
// 'appLoginFail' a few lines above).
$ulogin = new uLogin('appLogin', 'appLoginFail');

if (!isAppLoggedIn() && isset($_POST['btnsubmit'])) {
    if(!$redirect) $redirect = 'index.php';

    if ($action=='login') {
        // Here we verify the nonce, so that only users can try to log in
        // to whom we've actually shown a login page. The first parameter
        // of Nonce::Verify needs to correspond to the parameter that we
        // used to create the nonce, but otherwise it can be anything
        // as long as they match.
        if (isset($_POST['nonce']) && ulNonce::Verify('login', $_POST['nonce'])){
            // We store it in the session if the user wants to be remembered. This is because
            // some auth backends redirect the user and we will need it after the user
            // arrives back.
            if (isset($_POST['autologin']))
                $_SESSION['appRememberMeRequested'] = true;
            else
                unset($_SESSION['appRememberMeRequested']);

            // This is the line where we actually try to authenticate against some kind
            // of user database. Note that depending on the auth backend, this function might
            // redirect the user to a different page, in which case it does not return.
            $ulogin->Authenticate($_POST['user'],  $_POST['pwd']);
            if ($ulogin->IsAuthSuccess()){
                // Since we have specified callback functions to uLogin,
                // we don't have to do anything here.
                header('Location:'. $redirect);
            }
        }else
            add_message( 'invalid nonce');

    } else if ($action=='autologin'){	// We were requested to use the remember-me function for logging in.
        // Note, there is no username or password for autologin ('remember me')
        $ulogin->Autologin();
        if (!$ulogin->IsAuthSuccess())
            add_message ('autologin failure');
        else{
            add_message ('autologin ok');
            header('Location:'. $redirect);
        }

    } else if ($action=='create'){	// We were requested to try to create a new acount.
        // New account
        if ( !$ulogin->CreateUser( $_POST['user'],  $_POST['pwd']) )
            add_message ('account creation failure');
        else
            add_message ('account created');
    }
}
// First we handle application logic. We make two cases,
// one for logged in users and one for anonymous users.
// We will handle presentation after our logic because what we present is
// also based on the logon state, but the application logic might change whether
// we are logged in or not.

if (isAppLoggedIn()){
    if ($action=='delete')	{	// We've been requested to delete the account

        // Delete account
        if ( !$ulogin->DeleteUser( $_SESSION['uid']) )
            add_message ('account deletion failure');
        else
            add_message ('account deleted ok');

        // Logout
        appLogout();
    } else if ($action == 'logout'){ // We've been requested to log out
        // Logout
        appLogout();
        add_message ('logged out');
    }
}
?>
<html>
<head>
    <title>Dashboard Cpanel</title>
    <?php include ('template/head.php')?>
</head>
<body>
<div class="wrapper">
    <?php
    //include header
    include ('template/header.php');

    show_messages();
    // This inserts a few lines of javascript so that we can debug session problems.
    // This will be very usefull if you experience sudden session drops, but you'll
    // want to avoid using this on a live website.
    ulLog::ShowDebugConsole();
    ?>
    <div class="main">
    <?php if(isAppLoggedIn() ){?>
        <p>dfsdgkdhfghiivsidgidgdfhghdg</p>
        <p>dfsdgkdhfghiivsidgidgdfhghdg</p>
        <p>dfsdgkdhfghiivsidgidgdfhghdg</p>
        <p>dfsdgkdhfghiivsidgidgdfhghdg</p>
    <?php }else{?>
    <h3>ĐĂNG NHẬP TÀI KHOẢN</h3>
    <?php echo ($msg);?>
    <form method="POST" class="login-form">
        <table>

            <tr>
                <td>
                    Username:
                </td>
                <td>
                    <input type="text" name="user">
                </td>
            </tr>

            <tr>
                <td>
                    Password:
                </td>
                <td>
                    <input type="password" name="pwd">
                </td>
            </tr>

            <tr>
                <td>
                    Remember me:
                </td>
                <td>
                    <input type="checkbox" name="autologin" value="1">
                </td>
            </tr>

            <tr>
                <td>
                    Action:
                </td>
                <td>
                    <select name="action">
                        <option>login</option>
                        <option>autologin</option>
                        <option>create</option>
                    </select>
                </td>
            </tr>

            <tr>

                <td colspan="2">
                    <input type="hidden" id="nonce" name="nonce" value="<?php echo ulNonce::Create('login');?>">
                </td>
            </tr>

            <tr>
                <td>
                    <input type="submit" NAME="btnsubmit" value="Submit">
                </td>
            </tr>

        </table>
    </form>
    <?php }?>
        </div>
</div>
</body>
</html>