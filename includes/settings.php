<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 17:05
 */
/**
 * Libs path
 */
define('HWCP_LIBS_PATH', dirname(dirname(__FILE__)). '/libs');
//include classes file
define('HWCP_CLASSES_PATH', dirname(__FILE__). '/classes');

/**
 * through composer library
 */
include (HWCP_LIBS_PATH.'/vendor/autoload.php');   //
/**
 * ADBDb
 */
include (HWCP_LIBS_PATH.'/adodb5/adodb.inc.php');
include (HWCP_CLASSES_PATH.'/database.class.php');   //database mysql
//php hooks
include_once (HWCP_LIBS_PATH. '/php-hooks.php');
/**
 * twig template
 */
include (HWCP_CLASSES_PATH.'/twig_template.class.php');   //

// Create connection
global $DB;
$conn= HW_DB::connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);


// twig template
global $twig;
HW_Twig_engine::init(ROOT_DIR.'/template');