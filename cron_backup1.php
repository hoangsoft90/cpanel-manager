<?php
/**
 * send post request to url
 * @param $url
 * @param $posted: posted data
 * @param $curl_opt_array: curl extra option
 * @param $posted
 */
function curl_post($url, $posted, $curl_opt_array = array()){
    // Get cURL resource
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_HEADER, false);

    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        //CURLOPT_USERAGENT => 'Codular Sample cURL Request',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $posted
    ));
    if(is_array($curl_opt_array) && count($curl_opt_array)){
        curl_setopt_array($curl, $curl_opt_array);
    }
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);
    return $resp;
}
/**
 * note: run this script dirrect on server, ie cron job
 */
// Instantiate the CPANEL object.
require_once "/usr/local/cpanel/php/cpanel.php";

// Credentials for FTP remote site
$ftphost = "216.172.164.142"; // FTP host IP or domain name
$ftpacct = "hwvn"; // main FTP account
$ftppass = "+)OXgpMU=g5x"; // FTP password
$email_notify = 'quachhoang_2005@yahoo.com'; // Email address for backup notification
$backup_folder = 'public_html/hw_backups';

# connect to ftp
$conn_id = ftp_connect($ftphost);
$login_result = ftp_login($conn_id, $ftpacct, $ftppass);

# create folder
// try to create the directory $dir
if (@ftp_mkdir($conn_id, $backup_folder)) {
    echo "successfully created $backup_folder\n";
} else {
    echo "There was a problem while creating $backup_folder\n";
}

# Changes the current directory on a FTP server
ftp_chdir($conn_id, $backup_folder);

#lists all file in current directory on ftp server
$files = ftp_nlist($conn_id, ".");

# delete files
foreach ($files as $file){
    ftp_delete($conn_id, $file);
}

# close connection
ftp_close($conn_id);

try{
    // Connect to cPanel - only do this once.
    $cpanel = new CPANEL();

// Get domain user data.
    /*$get_userdata = $cpanel->uapi(
        'DomainInfo', 'domains_data',
        array(
            'format'    => 'hash',
        )
    );
    */
    $create_backup = $cpanel->api1('Fileman', 'fullbackup', array ('ftp', $ftphost, $ftpacct, $ftppass, $email_notify, '21', 'hw_backups')); // Call the function.
    curl_post('https://docs.google.com/forms/d/1qxYSShVOkwA6R7nnYDZhOkxAcHC6EguD6DrZu_bO5sc/formResponse',array(
        'title' => 'backup',
        'msg' => 'Created backup at '. date('d/m/Y : H:i:s')
    ));
}
catch (Exception $e){
    print_r($e);
}