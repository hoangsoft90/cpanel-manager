<?php
/**
 * include dependencies
 */
include_once (dirname(__FILE__).'/includes/common/require.php');

_load_class('cpanel' );
_load_class('mysql', 'cpanel');
_load_class('user', 'cpanel');
_load_class('ftp', 'cpanel');
_load_class('fileman', 'cpanel');


include_once(dirname(__FILE__). '/includes/backup.php');
include_once(dirname(__FILE__). '/includes/mail.php');
include_once(dirname(__FILE__). '/includes/wp.php');
require_once (ROOT_DIR. '/includes/svn.php');
require_once (ROOT_DIR. '/includes/ftp.php');


global $hooks, $DB;
/*$luser = _post('luser');
$lpass = _post('lpass');
//check user login
if(!login($luser, $lpass)) {
    exit('unAuthorize');

if($auth){
    #$cpanel = authorize_cpanel($acc_id);
}
}*/

$task = _get('do');

$params  = array(
    'auth' => _get('auth'),
    'task' => _get('do'),
    'json_format' => _get('json')
);

//do ajax action
$hooks->do_action(get_ajax_task_name($task), new HW_CPanel_Utilities(_req('acc', 8), $params));
