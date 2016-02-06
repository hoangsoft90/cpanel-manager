<?php
/**
upload this file & dependencies to server

 */
// This is the one and only public include file for uLogin.
// Include it once on every authentication and for every protected page.
require_once('libs/ulogin/config/all.inc.php');
require_once('libs/ulogin/main.inc.php');

/**
 * authenticate
 * @param $user
 * @param $pass
 */
function login($user,$pass) {
    $ulogin = new uLogin('', '');
    $ulogin->Authenticate($user, $pass);
    return $ulogin->IsAuthSuccess();
}
/**
 * decrypt string
 * @param $str string to decrypt
 * @return string
 */
function decrypt($str) {
    return base64_decode($str);
}
#-----------------------------------------------------------------------------------------------------------------
$luser = isset($_POST['user'])? $_POST['user'] : '';
$lpass= isset($_POST['pass'])? $_POST['pass'] : '';

//valid session
if(!login($luser, $lpass)) {
    exit("Invalid credentials.");
}

//get from database
$db = new SQLite3('db/db');
$r = $db->query("select hash from hashtext limit 1");
list($hash) = $r->fetchArray(SQLITE3_NUM);
$data = decrypt($hash);
$data = unserialize($data);
#print_r($data);exit();

// Credentials for FTP remote site
$acc_id = isset($_REQUEST['acc'])? $_REQUEST['acc'] : '';
$acc = array();
foreach ($data as $row) {
    if($row['id'] == $acc_id) {
        $acc = $row;
        break;
    }
}

$ftphost = isset($acc['cpanel_host'])? $acc['cpanel_host']: (isset($_POST['host'])? $_POST['host']:""); // FTP host IP or domain name
$ftpacct = isset($acc['cpanel_user'])? $acc['cpanel_user']: (isset($_POST['user'])? $_POST['user'] : ""); // FTP account
$ftppass = isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']): (isset($_POST['pass'])? $_POST['pass'] : ""); // FTP password
$email_notify = isset($acc['cpanel_email'])? $acc['cpanel_email']: (isset($_POST['email_notify'])? $_POST['email_notify'] : "hoangsoft90@gmail.com"); // Email address for backup notification
$backup_folder = '/public_html/hw_backups';

//validation
if(empty($ftphost) || empty($ftpacct) || empty($ftppass)) {
    exit("Provide full credentials ?");
}
#echo $ftphost,',',$ftpacct,',',$ftppass;

/**
 * create cpanel instance
 */
include_once('libs/xmlapi.php');
$xmlapi = new xmlapi($ftphost);
#$xmlapi->hash_auth("root",$root_hash);
$xmlapi->password_auth($ftpacct,$ftppass);
$xmlapi->set_port(2083);
$xmlapi->set_output("json");

/**
 * delete old backup file
 */
# connect to ftp
$conn_id = @ftp_connect($ftphost);
$login_result = @ftp_login($conn_id, $ftpacct, $ftppass);

# Changes the current directory on a FTP server
#ftp_chdir($conn_id, '/public_html');

// try to create the directory $dir
if (@ftp_mkdir($conn_id, $backup_folder)) {
    echo "successfully created $backup_folder\n";
} else {
    echo "There was a problem while creating $backup_folder\n";
}

# Changes the current directory on a FTP server
#$logs_dir = "/public_html/abc";
@ftp_chdir($conn_id, $backup_folder);

#lists all file in current directory on ftp server
$files = @ftp_nlist($conn_id, ".");

# delete files
if(is_array($files))
foreach ($files as $file){
    @ftp_delete($conn_id, $file);
}

# close connection
@ftp_close($conn_id);

/**
 * create new backup
 */
$api_args = array(
    'passiveftp',
    $ftphost,
    $ftpacct,
    $ftppass,
    $email_notify,
    21,
    $backup_folder  # save to ?
);

$xmlapi->set_output('json');
$result= $xmlapi->api1_query($ftpacct,'Fileman','fullbackup',$api_args);
print_r($result) ;