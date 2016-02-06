<?php
// We define some functions to log in and log out,
// as well as to determine if the user is logged in.
// This is needed because uLogin does not handle access control
// itself.

function isAppLoggedIn(){
    return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn']===true);
}

/**
 * logout
 */
function appLogout(){
    // When a user explicitly logs out you'll definetely want to disable
    // autologin for the same user. For demonstration purposes,
    // we don't do that here so that the autologin function remains
    // easy to test.
    //$ulogin->SetAutologin($_SESSION['username'], false);

    unset($_SESSION['uid']);
    unset($_SESSION['username']);
    unset($_SESSION['loggedIn']);
}
/**
 * when logined
 * @param $uid
 * @param $username
 * @param $ulogin
 */
function appLogin($uid, $username, $ulogin){
    $_SESSION['uid'] = $uid;
    $_SESSION['username'] = $username;
    $_SESSION['loggedIn'] = true;

    if (isset($_SESSION['appRememberMeRequested']) && ($_SESSION['appRememberMeRequested'] === true))
    {
        // Enable remember-me
        if ( !$ulogin->SetAutologin($username, true))
            add_message ("cannot enable autologin<br>");

        unset($_SESSION['appRememberMeRequested']);
    }
    else
    {
        // Disable remember-me
        if ( !$ulogin->SetAutologin($username, false))
            add_message ('cannot disable autologin<br>');
    }
}

/**
 * login failed
 * @param $uid
 * @param $username
 * @param $ulogin
 */
function appLoginFail($uid, $username, $ulogin){
    // Note, in case of a failed login, $uid, $username or both
    // might not be set (might be NULL).
    add_message( 'login failed<br>');
}

/**
 * valid user session
 */
function check_user_session() {
    @session_start();
    //only for administrator
    if(!isAppLoggedIn() || @$_SESSION['username'] != 'hoangwebadmin') {
        header('Location:login.php?code=not_permission');
    }
}

/**
 * authenticate
 * @param $user
 * @param $pass
 */
function login($user,$pass) {
    $ulogin = new uLogin('appLogin', 'appLoginFail');
    $ulogin->Authenticate($user, $pass);
    return $ulogin->IsAuthSuccess();
}

