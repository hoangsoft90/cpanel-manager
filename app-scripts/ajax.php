<?php
#put this file on httdoc root yourdomain/ajax.php
/**
 * encrypt string
 * @copy /cpanel/includes/common.php
 * @param $str string to encrypt
 * @return string
 */
function encrypt($obj) {
    /*$ENCRYPTION_KEY ='d0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282';
    #$encrypt = is_array($obj)||is_object($obj)? serialize($obj) : $obj;
    $encrypt = serialize($obj);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*', $ENCRYPTION_KEY);
    $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);*/

    return base64_encode($obj);
    #return $encoded;
}

/**
 * decrypt string
 * @copy /cpanel/includes/common.php
 * @param $str string to decrypt
 * @return string
 */
function decrypt($str) {
    /*$ENCRYPTION_KEY ='d0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282';
    $decrypt = explode('|', $str.'|');
    $decoded = base64_decode($decrypt[0]);
    $iv = base64_decode($decrypt[1]);
    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    $key = pack('H*', $ENCRYPTION_KEY);
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if($calcmac!==$mac){ return false; }
    $decrypted = unserialize($decrypted);*/

    return base64_decode($str);
    #return $decrypted;
}
/**
 * return GET data
 * @copy /cpanel/includes/common.php
 * @param $param
 * @param string $def
 * @return string
 */
function _get($param,$def=''){
    return isset($_GET[$param])? $_GET[$param]: $def;
}

/**
 * return POST data
 * @copy /cpanel/includes/common.php
 * @param $param
 * @param string $def
 * @return string
 */
function _post($param,$def='') {
    return isset($_POST[$param])? $_POST[$param]: $def;
}

/**
 * get/post variable
 * @copy /cpanel/includes/common.php
 * @param $param
 * @param string $def
 */
function _req($param, $def='') {
    return isset($_REQUEST[$param])? $_REQUEST[$param]: $def;
}
/**
 * echo json encode data
 * @copy /cpanel/includes/common.php
 * @param $data
 */
function ajax_output($data, $break_line=false) {
    if(is_array($data)) echo json_encode($data);
    else print_r( $data);
    if($break_line) echo '<hr/>';
}
#------------------------------------
$task = _req('do');
/**
 * decode hash string
 */
if($task == 'decpass') {
    echo decrypt(_req('str'));
}
/**
 * svn decode pass from svn-create-repo.sh
 */
elseif($task =='dec_svn_pass') {
    $t=explode(PHP_EOL, _req('str'));    #if failt try "\r\n"
    if(count($t) && trim($t[0]) == 'svn_pass') {
        echo decrypt(trim($t[1]));
    }
}