<?php
#make script execution to unlimited
ini_set('max_execution_time', 0);
set_time_limit(0);



#--------------------Account-----------------------#
/**
 * WHM IP
 */
define('HW_WHM_IP', 'x.x.x.x');
define('HW_WHM_ROOT_USER', 'root');
define('HW_WHM_ROOT_PASS', '');	//nwvZ9b2CDBXP

/**
 * DATABASE
 */
define('DB_HOST',  'localhost');
define('DB_NAME', "hw_projects");
define('DB_USER',  "root");
define('DB_PASS',  "hoang");

#--------------------Path-----------------------#
/**
 * root path
 */
define('ROOT_DIR', dirname(dirname(__FILE__)));

/**
 * base url
 */
define('BASE_URL', 'http://localhost/cpanel');

/**
 * tmp path
 */
define('TMP_DIR', ROOT_DIR. '/tmp');

/**
 * cpanel shell app path
 */
define('WHM_CPANEL_SHELL_APP', 'E:\HoangData\HoangWeb\projects\whm-cpanel-shell');

/**
 * WP treasting tool
 */
define('HW_WP_TOOL_FILE', 'hw-wp-tool.php');

#--------------------Information-----------------------#
/**
 * generate strong password
 */
define('STRONG_PASS_LENGTH', '15');

/**
 * using google drive to send mail
 * https://docs.google.com/forms/d/1{ID}/viewform
 */
define('TO_SEND_MAIL_GFORM_ID', '1MBdOeyg8FLRD9OwLAWxG3MpP60v82hZB2Hn_j0gEsMc');

/**
 * admin email
 */
define('ADMIN_EMAIL', 'hoangsoft90@gmail.com');
/**
 * include files
 */
include (dirname(dirname(__FILE__)). '/includes/settings.php');
