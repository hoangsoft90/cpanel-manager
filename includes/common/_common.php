<?php
/**
 * print object
 * @param $str
 */
function _debug($str) {
    var_dump($str);
}

/**
 * print object in textarea
 * @param $obj
 */
function _print($obj) {
    echo '<textarea>';
    print_r($obj);
    echo '</textarea>';
}
/*
 * add message
 */
function add_message($line) {
    global $messages;
    if(empty($messages)) $messages = array();
    $messages[]= $line;
}

/**
 * display messages
 */
function show_messages() {
    global $messages;
    if(is_array($messages) && count($messages)) {
        echo '<ul class="messages">';
        foreach($messages as $msg) {
            echo "<li>";
            print_r($msg);
            echo "</li>";
        }
        echo '</ul>';
    }

}

/**
 * get referer url
 */
function get_referer_url() {
    @session_start();#var_dump(get_current_url().'<>'.@$_SERVER['HTTP_REFERER']);
    if(!empty($_SERVER['HTTP_REFERER']) && get_current_url() != $_SERVER['HTTP_REFERER']) {
        $_SESSION['hw_referer_url']= $_SERVER['HTTP_REFERER'];
    }
    if(isset($_SESSION['hw_referer_url'])) return $_SESSION['hw_referer_url'] ;
}

/**
 * return current page url
 */
function get_current_url() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
/**
 * get filename from path
 */
if(!function_exists('hwt_get_filename')):
    function hw_get_filename($path) {
        return array_pop(explode('/', trim($path, '/')));
    }
endif;
/**
 * valid a string as url
 * @param $domain
 * @return string
 */
if(!function_exists(('hw_valid_url'))):
function hw_valid_url($domain) {
    if(substr( trim($domain), 0, 7 ) == 'http://') return $domain;
    if(substr( trim($domain), 0, 8 ) == 'https://') return $domain;

    return 'http://'. $domain;
}
endif;
/**
 * encrypt string
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
 * @param $param
 * @param string $def
 * @return string
 */
function _get($param,$def=''){
    return isset($_GET[$param])? $_GET[$param]: $def;
}

/**
 * return POST data
 * @param $param
 * @param string $def
 * @return string
 */
function _post($param,$def='') {
    return isset($_POST[$param])? $_POST[$param]: $def;
}

/**
 * get/post variable
 * @param $param
 * @param string $def
 */
function _req($param, $def='') {
    return isset($_REQUEST[$param])? $_REQUEST[$param]: $def;
}
/**
 * echo json encode data
 * @param $data
 */
function ajax_output($data, $break_line=false) {
    if(is_array($data)) echo json_encode($data);
    else print_r( $data);
    if($break_line) echo '<hr/>';
}


/**
 * generate strong password
 * @param int $length
 * @param bool $add_dashes
 * @param string $available_sets
 * @return string
 */
function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
{
    $sets = array();
    if(strpos($available_sets, 'l') !== false)
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    if(strpos($available_sets, 'u') !== false)
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    if(strpos($available_sets, 'd') !== false)
        $sets[] = '23456789';
    if(strpos($available_sets, 's') !== false)
        $sets[] = '!@#$%&*?';
    $all = '';
    $password = '';
    foreach($sets as $set)
    {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
    }
    $all = str_split($all);
    for($i = 0; $i < $length - count($sets); $i++)
        $password .= $all[array_rand($all)];
    $password = str_shuffle($password);
    if(!$add_dashes)
        return $password;
    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while(strlen($password) > $dash_len)
    {
        $dash_str .= substr($password, 0, $dash_len) . '-';
        $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
}
#echo generateStrongPassword(15);
/**
 * call url by curl
 * @param $url
 * @param $extra_curl_opts extra curl options
 */
function curl_get($url, $extra_curl_opts = array()) {
    $curl = curl_init();                                // Create Curl Object
    #curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
    #curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
    curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);  //if redirect to other url

    curl_setopt($curl, CURLOPT_URL, $url);            // execute the query
    //custom curl options
    if(is_array($extra_curl_opts) && count($extra_curl_opts)){
        curl_setopt_array($curl, $extra_curl_opts);
    }
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/**
 * @param $url
 * @param $data
 * @param $extra_curl_opts
 */
function curl_post($url, $data, $extra_curl_opts=array()) {
    $curl = curl_init();                                // Create Curl Object
    #curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
    #curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
    curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec

    curl_setopt($curl, CURLOPT_URL, $url);            // execute the query
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);  //if redirect to other url

    //custom curl options
    if(is_array($extra_curl_opts) && count($extra_curl_opts)){
        curl_setopt_array($curl, $extra_curl_opts);
    }
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/**
 * send mail using google form
 * @param $to
 * @param $subject
 * @param $body
 * @param string $shortdesc
 * @return mixed
 */
function send_mail1($to,$subject,$body, $shortdesc='') {
    $url = "https://docs.google.com/forms/d/".TO_SEND_MAIL_GFORM_ID."/formResponse";
    $post = array(
        'entry.343949848' => $to,
        'entry.1355297923' => $to,

        'entry.1198893757' => $subject. '-' .$shortdesc,
        'entry.2042320298' => $body,    //field: sendEmail
        'entry.1998434718' => $body,    //field:tin nhan
        'entry.1379441142' => get_current_url()
    );
    $opt = array(
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    );
    return curl_post($url, $post, $opt);
}
/**
 * send mail
 * @param $to
 * @param $subject
 * @param $body
 * @param string $shortdesc
 */
function send_mail($to,$subject,$body, $shortdesc='') {

    require_once 'libs/PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output
    if(empty($to)) $to =  ADMIN_EMAIL;

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.mail.yahoo.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'quachhoang_2005@yahoo.com';                 // SMTP username
    $mail->Password = 'Hoangcode837939';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    $mail->From = 'quachhoang_2005@yahoo.com';
    $mail->FromName = 'Hoangweb Technical';
    $mail->addAddress($to);     // Add a recipient

    #$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    #$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML

    #$mail->WordWrap = 50;

    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = $shortdesc;

    if(!$mail->send()) {
        return 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        return 'Message has been sent to '.$to;
    }
}


/**
 * display data in table
 * @param array $data
 * @param array $fields
 * @param $callback
 */
function hw_output_table_data($data = array(), $fields=array(), $callback = null) {

    echo '<table border="1px" cellpadding="3px" cellspacing="1px">';
    echo '<tr>';
    foreach($fields as $name => $text) {
        echo "<td><strong>{$text}</strong></td>";
    }

    foreach($data as $item) {
        $item = (array) $item;
        echo '<tr>';
        foreach($fields as $name => $t) {
            if(isset($item[$name])) $value = $item[$name];
            else $value ='';

            if(is_callable($callback)) {
                $value = call_user_func($callback, $name, $value);
            }
            if($value) echo "<td>{$value}</td>";
            else echo "<td><em>Item '$name' not found.</em></td>";
        }

        echo '</tr>';
    }
    echo '</table>';
}

/**
 * @param $id
 * @param string $namespace
 */
function _load_class($id, $namespace='') {
    $file = ($namespace? $namespace.'.': '' ). preg_replace('%\.php$%', '', trim($id)). '.class.php';
    if(file_exists(HWCP_CLASSES_PATH. '/'.$file)) include_once (HWCP_CLASSES_PATH. '/'.$file);
}

/**
 * @param $name
 * @param $callback
 */
function add_action($name, $callback, $priority=1, $accepted_args=1) {
    global $hooks;
    if(is_callable($callback)) $hooks->add_action($name,$callback, $priority, $accepted_args);
}

/**
 * @param $name
 */
function do_action($name) {
    global $hooks;
    $hooks->do_action($name);
}

/**
 * @param $name
 * @param $callback
 */
function remove_action($name, $callback) {
    global $hooks;
    $hooks->remove_action($name, $callback);
}

/**
 * @param $name
 * @param $callback
 */
function add_filter($name, $callback, $priority=1, $accepted_args=1) {
    global $hooks;
    $hooks->add_filter($name, $callback, $priority, $accepted_args);
}

/**
 * @param $name
 * @return string
 */
function get_ajax_task_name($name) {
    return "ajax_task_$name";
}
