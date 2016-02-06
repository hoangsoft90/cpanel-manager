<?php
/**
 * include wp environment
 */
include 'wp-load.php';
require_once('wp-admin/includes/user.php' );

/**
 * return current page url
 */
if(! function_exists('get_current_url')) :
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
endif;
/**
 * get filename from path
 */
if(!function_exists('hwt_get_filename')):
function hwt_get_filename($path) {
    return array_pop(explode('/', trim($path, '/')));
}
endif;
/**
 * encrypt string
 * @param $str string to encrypt
 * @return string
 */
if(!function_exists('hwt_encrypt')):
function hwt_encrypt($obj) {
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
endif;
/**
 * decrypt string
 * @param $str string to decrypt
 * @return string
 */
if(! function_exists('hwt_decrypt')):
function hwt_decrypt($str) {
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
endif;
/**
 * send mail
 * @param $to
 * @param $subject
 * @param $body
 * @param string $shortdesc
 */
if(! function_exists('hwt_send_mail')) :
function hwt_send_mail($to,$subject,$body, $shortdesc='') {
    $gformid = '1MBdOeyg8FLRD9OwLAWxG3MpP60v82hZB2Hn_j0gEsMc';
    $url = "https://docs.google.com/forms/d/".$gformid."/formResponse";
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
    return hwt_curl_post($url, $post, $opt);
}
endif;
/**
 * call url by curl
 * @param $url
 * @param $extra_curl_opts extra curl options
 */
if(! function_exists('hwt_curl_get')) :
function hwt_curl_get($url, $extra_curl_opts = array()) {
    $curl = curl_init();                                // Create Curl Object
    #curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
    #curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
    curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec

    curl_setopt($curl, CURLOPT_URL, $url);            // execute the query
    //custom curl options
    if(is_array($extra_curl_opts) && count($extra_curl_opts)){
        curl_setopt_array($curl, $extra_curl_opts);
    }
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}
endif;
/**
 * @param $url
 * @param $data
 * @param $extra_curl_opts
 */
if(! function_exists('hwt_curl_post')) :
function hwt_curl_post($url, $data, $extra_curl_opts=array()) {
    $curl = curl_init();                                // Create Curl Object
    #curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
    #curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
    curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec

    curl_setopt($curl, CURLOPT_URL, $url);            // execute the query
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //custom curl options
    if(is_array($extra_curl_opts) && count($extra_curl_opts)){
        curl_setopt_array($curl, $extra_curl_opts);
    }
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}
endif;
/**
 * return GET data
 * @param $param
 * @param string $def
 * @return string
 */
if(! function_exists('hwt_get')) :
function hwt_get($param,$def=''){
    return isset($_GET[$param])? $_GET[$param]: $def;
}
endif;
/**
 * return POST data
 * @param $param
 * @param string $def
 * @return string
 */
if(! function_exists('hwt_post')) :
function hwt_post($param,$def='') {
    return isset($_POST[$param])? $_POST[$param]: $def;
}
endif;
/**
 * get/post variable
 * @param $param
 * @param string $def
 */
if(! function_exists('hwt_req')) :
function hwt_req($param, $def='') {
    return isset($_REQUEST[$param])? $_REQUEST[$param]: $def;
}
endif;
/**
 * echo json encode data
 * @param $data
 */
if(! function_exists('ajax_output')) :
function ajax_output($data, $break_line=false) {
    if(is_array($data)) echo json_encode($data);
    else print_r( $data);
    if($break_line) echo '<hr/>';
}
endif;

/**
 * add new or update exist row in table
 * @copy extend from /cpanel/includes/common.php
 * @param string $table table name
 * @param array $data
 * @param array $where where data, if not given get $data default
 */
function hwt_update_dbtable($table,$data=array(), $where=array()){
    global $wpdb;
    $insert_cols = '';
    $insert_vals = '';
    $update_keys = '';
    $update_values = array();
    $wheres= '';

    //prepare where clause
    if(is_array($where) && count($where)) {
        foreach($where as $col => $val) {
            $wheres .= $col.'="'.esc_sql($val).'" and ';
        }
    }
    foreach($data as $col => $val) {
        if($val === '') continue;
        $insert_cols .= $col.',';
        $insert_vals .= "'".esc_sql($val)."',";

        $update_keys .= $col.'=%s ,';
        $update_values[]= $val;

        //prepare where clause
        if(!is_array($where) || count($where)==0) {
            $wheres .= $col.'=%s and ';
        }
    }
    //valid
    $insert_cols = trim($insert_cols,',') ;
    $insert_vals = trim($insert_vals,',') ;
    $update_keys = trim($update_keys, ',');
    $wheres = trim($wheres, ' and ');

    //insert if not exist
    $sql ="INSERT INTO `{$table}` ($insert_cols)
  SELECT * FROM (SELECT $insert_vals) AS tmp
  WHERE NOT EXISTS (SELECT * FROM `{$table}` WHERE {$wheres}) LIMIT 1;";
    $wpdb->query($sql);

    //also you want to update
    if(!$wpdb->rows_affected) {
        $sql ="UPDATE `{$table}` set {$update_keys} WHERE {$wheres}";#echo $sql;_print($update_values);
        $wpdb->query($wpdb->prepare($sql, $update_values));
    }

    return $wpdb->rows_affected;
}
#----------------------------------------User-----------------------------------------------------------------
/**
 * add new or update exist svn user
 * @copy extend from cpanel/includes/functions-user.php
 * @param array $data
 * @param array $where where data, if not given get $data default
 */
if(!function_exists('hwt_svn_update_user')):
function hwt_svn_update_user($data=array(), $where=array()){
    global $wpdb;
    $insert_cols = '';
    $insert_vals = '';
    $update_keys = '';
    $update_values = array();
    $wheres= '';

    //prepare where clause
    if(is_array($where) && count($where)) {
        foreach($where as $col => $val) {
            $wheres .= $col."='".esc_sql($val)."' and ";
        }
    }
    foreach($data as $col => $val) {
        if($val === '') continue;
        $insert_cols .= $col.',';
        $insert_vals .= "'{$val}',";

        $update_keys .= $col.'=%s ,';
        $update_values[]= $val;

        //prepare where clause
        if(!is_array($where) || count($where)==0) {
            $wheres .= $col."='".esc_sql($val)."' and ";
        }
    }
    //valid
    $insert_cols = trim($insert_cols,',') ;
    $insert_vals = trim($insert_vals,',') ;
    $update_keys = trim($update_keys, ',');
    $wheres = trim($wheres, ' and ');

    //insert if not exist
    $sql ="INSERT INTO `svn_wp_users` ($insert_cols)
SELECT * FROM (SELECT $insert_vals) AS tmp
WHERE NOT EXISTS (
    SELECT * FROM `svn_wp_users` WHERE {$wheres}) LIMIT 1;";
    $wpdb->query($sql);

    //also you want to update
    if(!$wpdb->rows_affected) {
        $sql ="UPDATE `svn_wp_users` set {$update_keys} WHERE {$wheres}";#echo $sql;

        $wpdb->query($wpdb->prepare($sql, $update_values));
    }

    return $wpdb->rows_affected;
}
endif;
/**
 * get user by
 * @copy extend from  cpanel/includes/functions-user.php
 * @param array $arg
 */
if(!function_exists('hwt_svn_get_user')):
function hwt_svn_get_user($arg= array()) {
    global $wpdb;
    $where='';
    $values = array();
    foreach($arg as $key => $val) {
        $where .= $key.'=%s and ';
        $values[] = $val;
    }
    //valid
    $where = trim($where, ' and ');
    $sql =$wpdb->prepare("SELECT * FROM svn_wp_users WHERE {$where} limit 1", $values);
    $rs= $wpdb->get_row($sql );
    return $rs;
}
endif;
/**
 * delete svn user by id
 * @copy extend from cpanel/includes/functions-user.php
 * @param $id
 */
if(!function_exists('hwt_svn_deluser') ):
function hwt_svn_deluser($id) {
    global $wpdb;
    $wpdb->delete('svn_wp_users', array('id'=>$id));
    return $wpdb->rows_affected;
}
endif;
/**
 * get repositories
 * @copy extend from cpanel/includes/functions-svn.php
 * @param array $arg
 * @param int $limit
 */
function hwt_svn_get_repositories($arg = array(), $limit='') {
    global $wpdb;
    $t_repo = 'svn_repositories';
    $t_u = 'svn_wp_users';

    $where='';
    $values = array();
    foreach($arg as $key => $val) {
        $where .= $key.'=%s and ';
        $values[] = $val;
    }
    //valid
    $where = trim($where, ' and ');

    $sql = "select t_repo.id as repo_id,t_repo.repository_name,t_u.id as svn_user_id,t_u.svn_user,t_u.svn_fullname, t_u.svn_pass,t_u.svn_email,t_u.domain from {$t_repo} as t_repo left join {$t_u} as t_u on t_repo.svn_user = t_u.svn_user ";
    if($where) $sql.= ' WHERE '. $where;
    if($limit && is_numeric($limit)) $sql .= ' limit '.$limit;

    $sql = $wpdb->prepare($sql, $values);
    $rs = $limit==1? $wpdb->get_row($sql, ARRAY_A) : $wpdb->get_results($sql, ARRAY_A);

    return $rs;
}
#==================================================================================================
$acc_id = hwt_req('acc');  //account id
$task = hwt_get('do') ;   //get task
$domain = $_SERVER['HTTP_HOST'];    //current domain
$subdomain = hwt_post('subdomain'); //subdomain
$doc_root = $_SERVER['DOCUMENT_ROOT'];

#---------------------------------------------------------------------------------------------------
#   POSTS
#---------------------------------------------------------------------------------------------------

/**
 * delete post by id
 */
if($task == 'del_post') {
    $id = hwt_req('pid');
    if(is_numeric($id)) {
        $res = wp_delete_post($id);
        echo $res ;
    }
}

#---------------------------------------------------------------------------------------------------
#   USER
#---------------------------------------------------------------------------------------------------
/**
 * reset user password
 */
elseif($task == 'reset_user_pass') {
    $user = isset($_POST['user'])? $_POST['user'] : '';
    $pass = isset($_POST['pass'])? ($_POST['pass']) : '';
//valid
    if(!is_numeric($user)) {
        $_user = get_user_by('login', $user);
        if($_user) $user = $_user->ID;
    }
    if(is_numeric($user)) {
        wp_set_password($pass, $user);
        $body ="Thông tin chi tiết tài khoản đăng nhập mới:<br/>
            <ul>
                <li>User: <strong>{$_user->user_login}</strong></li>
                <li>Pass: <strong>{$pass}</strong></li>
            </ul>
                ";
        #hwt_send_mail($_user->user_email, 'Hoangweb gửi tài khoản user', $body);   //because sent mail from client /cpanel/ajax.php
        ajax_output(array('user'=>$_user->user_login, 'pass' => $pass, 'email'=> $_user->user_email));
    }

}
/**
 * create/update wp user
 */
elseif($task =='create_user') {
    #$user_id = isset($_POST['user_id'])? $_POST['user_id'] : '';    //for updating user
    $user = isset($_POST['user'])? $_POST['user'] : '';
    $pass = isset($_POST['pass'])? $_POST['pass'] : '';
    $email = isset($_POST['email'])? $_POST['email'] : '';
    $role = isset($_POST['role'])? $_POST['role'] : 'contributor';
    $fullname = hwt_post('fullname', 'hoangweb test-'.$user);

    $_user = get_user_by('login', $user);
    if($_user && count($_user)) {
        $id = $_user->ID;   //user id
        wp_update_user(array(
            'ID' => $_user->ID,
            'role' => $role,
            'user_pass' => $pass,
            'user_email' => $email,
            'display_name' => $fullname
        ));
    }
    else {
        $id = wp_create_user($user, $pass, $email);
        if(is_numeric($id)) {
            wp_update_user(array(
                'ID' => $id,
                'role' => $role ,
                'description' => 'Created by hoangweb',
                'display_name' => $fullname
            ) );
        }
        elseif(is_wp_error($id)) {
            ajax_output($id);
            exit();
        }

    }
    //update user meta
    #disable admin front bar
    update_user_meta( $id, 'show_admin_bar_front', 'false' );
    update_user_meta( $id, 'show_admin_bar_admin', 'false' );

    //update svn user
    if(hwt_post('update_svn_user') ) {
        $data = array('svn_user' => $user, 'domain' => $_SERVER['HTTP_HOST']);
        if($pass) $data['svn_pass'] = hwt_encrypt($pass);
        if($fullname) $data['svn_fullname'] = $fullname;
        if($email) $data['svn_email'] = $email;

        if(count($data)>1) hwt_svn_update_user($data, array('svn_user'=> $user));
    }

    //send alert email
    if(!is_wp_error($id) && is_numeric($id)) {
        #$admin = get_user_by('id',1);
        $body ="Thông tin chi tiết tài khoản đăng nhập mới:<br/>
            <ul>
                <li>User: <strong>{$user}</strong></li>
                <li>Pass: <strong>{$pass}</strong></li>
                <li>Email: <strong>{$email}</strong></li>
            </ul>
            ";
        #hwt_send_mail($email, 'Hoangweb gửi tài khoản user', $body);   //because sent mail from client /cpanel/ajax.php
        ajax_output(array('user' => $user, 'pass'=>$pass, 'email'=>$email));
    }

}
/**
 * delete wp user
 */
elseif($task =='del_user') {

    $wp_userid = hwt_post('user_id');
    $update_svn_user = hwt_post('update_svn_user');
    $svn_user = hwt_post('svn_user');
    //valid
    if($svn_user && !$wp_userid) {
        $user = get_user_by('login', $svn_user);
        if($user) $wp_userid = $user->ID;   //because wp_users & svn_wp_users is same in user login.
    }

    //del wp user
    if($wp_userid) {
        $res = wp_delete_user($wp_userid);
        ajax_output(array('result'=> $res? "Deleted wp user `{$wp_userid}` on server." : "Unable to delete wp user {$wp_userid}"));
    }
    else ajax_output("No specific `user_id` param or `svn_user` ({$svn_user}) param for wp user not found.") ;

    //update `svn_wp_users` table
    if($update_svn_user && $svn_user) {
        $user = hwt_svn_get_user(array('svn_user' => $svn_user, 'domain' => $domain));
        if(count($user)) {
            $res = hwt_svn_deluser($user->id);
            ajax_output($res? "Delete svn user [{$svn_user}] from 'svn_wp_users' table successful.": "Delete svn user [{$svn_user}] from 'svn_wp_users' failt.");
        }
        else ajax_output("User {$svn_user} & domain {$domain} not found on `svn_wp_users` table.");
    }
}
/**
 * list wp users
 */
elseif($task =='list_users') {
    $users = get_users();
    if(hwt_req('json')=='1') {
        $result = array();
        foreach($users as $u) {
            $result[$u->ID] = array(
                'user_login' =>$u->user_login,
                'user_email'=>$u->user_email,
                'display_name' => $u->user_nicename,
            );
        }
        ajax_output($result);
        exit();
    }

    echo '<h2>All WP Users on the web</h2>';
    echo '<table class="hover-table" border="1px" cellpadding="3px" cellspacing="1px">';
    echo '<tr>
        <td><strong>ID</strong></td>
        <td><strong>user</strong></td>
        <td><strong>email</strong></td>
        <td><strong>role</strong></td>
        <td></td>

    </tr>';
    foreach($users as $user) {
        //delete user link
        $del_user_link= "<a href='javascript:void(0)' class='button' data-subdomain='{$subdomain}' onclick='hw_delete_wpuser(this, ".$acc_id.",".$user->ID.")'>Delete</a>";
        //update role
        $update_role = "<a href='javascript:void(0)' class='button' data-subdomain='{$subdomain}' onclick='hw_wp_update_user_role(this, ".$acc_id.",".$user->ID.")'>set role</a>";
        //set pass
        $set_pass="<a href='javascript:void(0)' class='button' data-user='{$user->user_login}' data-user_id='{$user->ID}' data-subdomain='{$subdomain}' onclick='hw_reset_wpadmin(this,{$acc_id})'>set pass</a>";

        echo '<tr>';
        echo "<td>{$user->ID}</td>";
        echo "<td>{$user->user_login}</td>";
        echo "<td>{$user->user_email}</td>";
        echo "<td>{$user->roles[0]}</td>";
        echo "<td>";
        echo "{$del_user_link}|{$update_role}|{$set_pass}";
        echo "</td>";

        echo '</tr>';
    }
    echo '</table>';
}
/**
 * list users roles
 */
elseif($task == 'list_users_roles') {
    global $wp_roles;
    $roles = $wp_roles->get_names();

    echo '<h2>All WP Users roles on the web</h2>';
    echo '<table border="1px" cellpadding="3px" cellspacing="1px" class="hover-table">';
    echo '<tr>
        <td><strong>name</strong></td>
        <td><strong>display</strong></td>

    </tr>';
    foreach($roles as $role => $display) {
        echo '<tr>';
        echo "<td>{$role}</td>";
        echo "<td>{$display}</td>";

        echo '</tr>';
    }
    echo '</table>';
}
/**
 * update user role
 */
elseif($task == 'update_user_role') {
    $user_id = hwt_post('user_id');
    $role = hwt_post('role');

    $user_id = wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );
    if(is_wp_error($user_id)) {
        echo "Update user role `{$role}` failt.";
    }
    else echo "Update user role `{$role}` successful.";
}
/**
 * list all svn users from `svn_wp_users` table
 */
elseif($task =='list_svn_users') {
    global $wpdb;
    $data = $wpdb->get_results('select * from `svn_wp_users`');
    ajax_output($data);
}
#-------------------------------------------------------------------------------------------------
#   SVN
#-------------------------------------------------------------------------------------------------
/**
 * del wp posts for `themes` post type
 */
elseif($task == 'svn_del_wp_theme') {
    $theme_name = hwt_req('theme_name');

    $posts = get_posts("post_type=themes&meta_key=theme_name&meta_value={$theme_name}");
    if($posts && count($posts )) {
        $res = wp_delete_post($posts[0]->ID);
        echo $res? "Deleted theme `{$theme_name}`" : "Delete `{$theme_name}` Failt !";
    }
}
/**
 * re-build svn_custom.conf file
 */
elseif($task == 'build_svn_custom.conf' ) {  //refer to domain for svn server running
    if(class_exists('HW_Theme_repository')) {
        $repo = new HW_Theme_repository();
        $public_svn = $repo->public_svn;    //root for svn serves
        $svn_users_path = $repo->svn_users_path;    //svn users data

        $svn_custom = '';
        $def_svn_user = '';
        //get first user
        $files = array_filter(glob($svn_users_path.'/*'), 'is_file');
        if(count($files)) {
            $def_svn_user = str_replace('.svn.htpasswd', '', hwt_get_filename($files[0]) );
        }

        $dirs = array_filter(glob($public_svn.'/themes/*'), 'is_dir');
        foreach ($dirs as $dir) {
            if($dir == '.' || $dir == '..') continue;
            //get repository name
            $theme_name = hwt_get_filename($dir);

            //if exists repository in working directory
            if($repo->check_exists_repo($theme_name)) {
                $row = hwt_svn_get_repositories(array('t_repo.repository_name' => $dir), 1);
                if($row && !empty($row['svn_user'])) {
                    $svn_user = $row['svn_user'];
                }
                else $svn_user = $def_svn_user;

                $svn_custom .= "<location /themes/{$theme_name}>
	DAV svn
	SVNPath /home/hangwebvnn/public_svn/themes/{$theme_name}/
	AuthType Basic
	AuthName \"SVN themes/{$theme_name}\"
	AuthUserFile /home/hangwebvnn/svn_users/{$svn_user}.svn.htpasswd
	Require valid-user
	ErrorDocument 401 'Sai username hoac mat khau.'
</location>\n";
            }
        }
        if(!empty($svn_custom)) {
            $svn_custom = "<IfModule mod_dav_svn.c>
   #begin location
   {$svn_custom}
   #end location
</IfModule>";
            echo $svn_custom;

        }

    }

}
/**
 * list all repositories on svn server
 */
elseif($task == 'svn_list_repositories') {
    $update_repo_db = hwt_post('update_repo_db');   //allow to update repositories table

    if(class_exists('HW_Theme_repository')) {
        $repo = new HW_Theme_repository();
        $public_svn = $repo->public_svn;    //root for svn serves
        $repo_path ='themes';
        $repositories_data = array();

        $repositories = hwt_svn_get_repositories();
        $repo_users = array();
        foreach ($repositories as $row) {
            $repo_users[$row['repository_name']] = $row;
        }

        $dirs = array_filter(glob($public_svn."/{$repo_path}/*"), 'is_dir');
        echo '<h2>All Repositories working on SVN server.</h2>';
        echo '<table border="1px" cellpadding="3px" cellspacing="1px" class="hover-table">';
        echo '<tr>
            <td><strong>Repository (/themes/)</strong></td>
            <td><strong>Fullname</strong></td>
            <td><strong>User</strong></td>
            <td><strong>Pass</strong></td>
            <td><strong>Email</strong></td>
            <td></td>

        </tr>';
        foreach ($dirs as $dir) {
            if($dir == '.' || $dir == '..') continue;
            //get repository name
            $theme_name = hwt_get_filename($dir);

            //if exists repository in working directory
            if($repo->check_exists_repo($theme_name)) {
                $row = isset($repo_users[$theme_name])? $repo_users[$theme_name] : array();
                //valid
                $fullname = (!isset($row['svn_fullname']))? $row['svn_fullname'] : '';
                $svn_pass = (!isset($row['svn_pass']))? decrypt($row['svn_pass']) : '';
                $svn_email = (!isset($row['svn_email']))? $row['svn_email'] : '';


                #$row = hwt_svn_get_repositories(array('t_repo.repository_name' => $theme_name), 1);
                if(isset($repo_users[$theme_name]) && !empty($repo_users[$theme_name]['svn_user'])) {
                    $svn_user = $repo_users[$theme_name]['svn_user'];
                }
                else $svn_user = '';

                //update repositories table data
                $repo_info = array(
                    'repository_name' => $theme_name
                );
                if($svn_user) $repo_info['svn_user'] = $svn_user;

                if($update_repo_db) {
                    $where = array('repository_name' => $theme_name);
                    $repositories_data[] = array($repo_info, $where);
                    //update `svn_repositories` table
                    hwt_update_dbtable('svn_repositories', $repo_info , $where);
                }

                echo '<tr>';
                echo "<td><a href=\"http://svn.hoangweb.vn/{$repo_path}/{$theme_name}\" target=\"_blank\">{$theme_name}</a></td>";
                echo "<td>{$fullname}</td>";
                echo "<td>{$svn_user}</td>";
                echo "<td>{$svn_pass}</td>";
                echo "<td>{$svn_email}</td>";
                echo "<td><a href='javascript:void(0)' data-acc=\"{$acc_id}\" data-repository=\"{$theme_name}\" onclick='hw_svn_delrepo(this)'>Delete repository</a></td>";

                echo '</tr>';

            }
        }
        echo '</table>';
        echo '<textarea style="position:absolute;visibility:hidden;">'.serialize($repositories_data).'</textarea>';
    }
}
#---------------------------------------------------------------------------------------------------------------
#   WP PLUGINS
#---------------------------------------------------------------------------------------------------------------
/**
 * list all actived plugins
 */
elseif($task == 'list_activation_plugins') {
    $the_plugs = get_option('active_plugins');
    echo '

  <style>
  #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
  #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
  #sortable li span { position: absolute; margin-left: -1.3em; }
  </style>

    ';
    echo '<div class="plugins-list"><ul id="sortable">';
    foreach($the_plugs as $key => $value) {
        $string = explode('/',$value); // Folder name will be displayed
        #echo '<li>'.$string[0] .'</li>';
        echo '<li class="ui-state-default" data-item="'.$value.'"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$value.'</li>';
    }
    echo '</ul>';
    echo '<a class="button" href="javascript:void(0)" data-subdomain="'.$subdomain.'" onclick="hw_saveorder_wpplugins(this, \''.$acc_id.'\', \'#sortable\')">Save</a>';
    echo '</div>';

}
/**
 * deactive all plugins
 */
elseif($task == 'deactive_plugins') {
    global $wpdb;

    if(hwt_get('rm_all') == '1') {
        $wpdb->query('delete from '.$wpdb->option.' where option_name="active_plugins"');   //or update_option('active_plugins','')
        ajax_output(array('result'=>'deactived all plugins'));
    }
    else{
        //what plugin to delete, more plugin separate by comma
        $removes = array_filter(explode(',',hwt_post('rm_plugins')));

        //get all plugins, get_option('active_plugins');
        $row = $wpdb->get_row('select option_value from '.$wpdb->option.' where option_name="active_plugins"');
        $active_plugins = $row->option_value;   //get all actived plugins

        if($active_plugins ) {
            $_active_plugins = unserialize($active_plugins);
            #if(is_array($active_plugins)) {
            foreach ($removes as $pl) {
                $key =array_search($pl, $_active_plugins);
                if($key!==false) unset($_active_plugins[$key]);
            }
            #}
            $active_plugins1 = serialize($_active_plugins);

            //check
            if($active_plugins1 != $active_plugins) {
                update_option('active_plugins', $_active_plugins);
            }
        }
        ajax_output(array('result'=>'deactived plugins '. hwt_post('rm_plugins')));
    }

}
/**
 * active plugins
 */
elseif($task == 'active_plugins') {
    $list = array_filter( explode(',', hwt_post('addlist')) );

    //get all actived plugins
    $active_plugins = get_option('active_plugins');
    $first = count($active_plugins);
    foreach($list as $pl) {
        if(!in_array($pl, $active_plugins)) {
            $active_plugins[] = $pl;
        }
    }
    if($first != count($active_plugins)) {
        update_option('active_plugins', $active_plugins);
    }
    ajax_output(array('result'=>'active plugins: '.hwt_post('addlist')));
}
/**
 * update order of plugins
 */
elseif($task =='update_order_plugins') {
    $plugins = hwt_post('plugins');
    update_option('active_plugins', $plugins);
    ajax_output(array('result'=>'update plugins in order of: '.$plugins));
}

#--------------------------------------------------------------------------------------------------------------
#   DATABASE
#--------------------------------------------------------------------------------------------------------------
/**
 * list db table data
 */
elseif($task == 'list_dbtable_data') {
    global $wpdb;
    $table = hwt_post('table');
    $result = $wpdb->get_results("select * from `{$table}`");

    echo "<h2>Data on table {$table}</h2>";
    if(count($result)) {
        $fields = array_keys((array)$result[0]);

        echo '<table class="hover-table" border="1px" cellpadding="3px" cellspacing="1px">';
        echo '<tr>';
        foreach ($fields as $f) {
            echo "<td><strong>{$f}</strong></td>";
        }
        echo '</tr>';

        foreach($result as $row) {
            $row = (array) $row;    //convert into array
            echo '<tr>';
            foreach ($fields as $f) {
                if($f=='svn_pass') $row[$f] = decrypt($row[$f]);
                echo "<td>{$row[$f]}</td>";
            }
            echo '</tr>';
        }
        echo '</table>';
    }
    else echo 'Empty data on table '. $table;
}
/**
 * return db user info from wp-config.php
 */
elseif($task =='dbuser_info') {
    $db = array(
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER,
        'DB_PASSWORD' => DB_PASSWORD,
    );
    ajax_output($db);
}
/**
 * export sql
 */
elseif ($task =='export_sql') {
    if(file_exists('dumper.php')) {
        include ('dumper.php');
        $name = hwt_post('file');
        try {
            $world_dumper = Shuttle_Dumper::create(array(
                'host' => DB_HOST,
                'username' => DB_USER,
                'password' => DB_PASSWORD,
                'db_name' => DB_NAME,
            ));

            // dump the database to gzipped file
            #$world_dumper->dump($name. '.sql.gz');     //.sql.gz not allow to import by mysql command

            // dump the database to plain text file
            $world_dumper->dump($name. '.sql');
            ajax_output(array('link' => home_url(). '/'. $name. '.sql.gz'));
        }
        catch(Shuttle_Exception $e) {
            ajax_output(array('result' => "Couldn't dump database: " . $e->getMessage() )) ;
        }
    }

}
/**
 * import sql
 */
elseif($task =='import_sql') {
    if(hwt_get('clear_all')) {

    }

    //ENTER THE RELEVANT INFO BELOW
    $mysqlDatabaseName = hwt_post('dbname')? hwt_post('dbname'): DB_NAME;
    $mysqlUserName = DB_USER;
    $mysqlPassword = DB_PASSWORD;
    $mysqlHostName = DB_HOST;
    $mysqlImportFilename = $_SERVER['DOCUMENT_ROOT'].'/'.hwt_post('file');    //please not allow .sql.gz file only for .sql file

    //DO NOT EDIT BELOW THIS LINE
    //Export the database and output the status to the page
    $command='mysql -h ' .$mysqlHostName .' -u ' .$mysqlUserName .' "-p' .$mysqlPassword .'" ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;

    exec($command,$output=array(),$worked);
    switch($worked){
        case 0:
            $msg= 'Import file <b>' .$mysqlImportFilename .'</b> successfully imported to database <b>' .$mysqlDatabaseName .'</b>';
            break;
        case 1:
            $msg= 'There was an error during import. Please make sure the import file is saved in the same folder as this script and check your values:<br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' .$mysqlDatabaseName .'</b></td></tr><tr><td>MySQL User Name:</td><td><b>' .$mysqlUserName .'</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' .$mysqlHostName .'</b></td></tr><tr><td>MySQL Import Filename:</td><td><b>' .$mysqlImportFilename .'</b></td></tr></table>';
            break;
        default: $msg = '';
    }
    unlink($mysqlImportFilename);   //remove file after executing
    ajax_output(array('result' => $msg));
}
/**
 * change maintenance mode via .htaccess
 */
elseif($task =='maintenance_mode') {
    $htaccess_file = $doc_root.'/.htaccess';
    $url = hwt_post('redirect_url', 'http://hoangweb.com');

    #$begin_locksite_comment ='# BEGIN HOANGWEB LOCKSITE';
    #$end_locksite_comment ='# END HOANGWEB LOCKSITE';

    //default htaccess content
    $htaccess = file_get_contents($htaccess_file);

    //lock site
    if(strpos($htaccess, "ErrorDocument 403 $url") === false) {
        //block code
        $append="# BEGIN HOANGWEB LOCKSITE\nErrorDocument 403 $url
Order deny,allow
Deny from all\n# END HOANGWEB LOCKSITE";

        $htaccess .= "\n\n{$append}";
    }
    //modify .htaccess
    file_put_contents($htaccess_file, $htaccess );

}
//del this file after execution
unlink(__FILE__);