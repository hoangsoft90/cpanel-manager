<?php
// This is an example that shows how to incorporate uLogin into a webpage.
// It showcases nonces, login authentication, account creation, deletion and
// remember-me functionality, all at the same time in a single page.
// Because of the number of functions shown and all the comments,
// it seems a little bit longish, but fear not.
//init
include_once(dirname(dirname(__FILE__)). '/config.php');
include_once(ROOT_DIR. '/includes/common/_common.php');

// This is the one and only public include file for uLogin.
// Include it once on every authentication and for every protected page.
require_once(ROOT_DIR.'/libs/ulogin/config/all.inc.php');
require_once(ROOT_DIR. '/libs/ulogin/main.inc.php');

include_once (ROOT_DIR. '/includes/cpanel.php');
require_once(ROOT_DIR. '/includes/user.php');
require_once (ROOT_DIR. '/includes/svn.php');
require_once (ROOT_DIR. '/includes/database.php');
require_once (ROOT_DIR. '/includes/files.php');


// Start a secure session if none is running
if (!sses_running())
    sses_start();
