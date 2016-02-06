<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 19:59
 */
/**
 * reset wp admin
 * @param $cp
 */
function reset_wpadmin($cp) {
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool($cp->acc_id, $subdomain);
    ajax_output($res);

    $wp_user = _post('user');
    $wp_pass = _post('pass');

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=reset_user_pass&acc='.$cp->acc_id;
    $data = array('user' => $wp_user, 'pass' => $wp_pass);
    $res = curl_post($url, $data);
    ajax_output($res);

    $result = json_decode($res);
    //save to db
    if(_post('savedb') && $result) {
        $myuser = array(
            'svn_user' => $wp_user,
            'svn_pass' => encrypt($wp_pass),   //alway encrypt pass in db
            'svn_email' => $result->email,
            'domain' => $cp->domain.(trim($subdomain,'/')? '/'.trim($subdomain,'/'):'')
        );
        //add new or update user
        $res = hw_svn_update_user($myuser, $wp_user);
        ajax_output($res? "Update svn user [{$myuser['svn_user']}] successful !":"Update svn user failt !");
    }

    //send mail
    if(_post('sendmail') && isset($result->email)) {
        $body = HW_Twig_engine::twig_render('email/reset_wpadmin.tpl', array(
            'domain' => $cp->domain,'username' => $cp->cpaneluser,'email_domain' => $cp->email_domain,
            'user' => $result->user, 'pass' => $result->pass
        ));
        send_mail1($result->email, 'Hoangweb.COM - Thay đổi mật khẩu đăng nhập', $body);
        ajax_output('Sent mail to '. $result->email);
    }
}
add_action('ajax_task_reset_wpadmin', 'reset_wpadmin');
/**
 * create wp user
 * @param $cp
 */
function create_wp_user($cp) {
    $subdomain = _post('subdomain');
    $wp_user = _post('user');
    $wp_pass = _post('pass');
    $wp_email = _post('email');
    $role = _post('role');
    $login_path = _post('login_path', '/login');

    $fullname= urldecode(_post('fullname'));
    $update_svn_user = _post('update_svn_user');

    //check for exist user in db, wp user is unique for all cpanel domain
    /*$exist_u = hw_svn_get_user(array(
        'svn_user' => $wp_user
    ));

    if($exist_u && count($exist_u)) {
        echo "Sory, exists user {$wp_user}. Choose a other name.";
        exit();
    }*/

    //update wp tool on wp root
    $res = $cp->upload_wptool($cp->acc_id, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=create_user&acc='.$cp->acc_id;
    $data = array(
        'user' => _post('user'), 'pass' => _post('pass'), 'email' => _post('email'), 'fullname' => $fullname,'role' => $role,
        'update_svn_user' => $update_svn_user
    );
    $res = curl_post($url, $data);
    $result = json_decode($res);
    ajax_output($res);

    //save to db option
    if(_post('savedb') && $result) {
        $myuser = array(
            'svn_user' => $wp_user,
            'svn_pass' => encrypt($wp_pass),   //alway encrypt pass in db
            'svn_fullname' => $fullname,
            'svn_email' => $wp_email,
            'domain' => $cp->domain.(trim($subdomain,'/')? '/'.trim($subdomain,'/'):'')
        );
        //add new or update user
        $_user= hw_svn_get_user(array('svn_user' => $wp_user));
        if($_user && count($_user)) { //update exist user
            $res = hw_svn_adduser($myuser, $_user['id']);
        }
        else $res = hw_svn_adduser($myuser);   //add new user

        #$res = hw_svn_update_user($myuser, array('svn_user' => $wp_user ));    //other way
        ajax_output($res? "Add svn user [{$myuser['svn_user']}] successful !":"Fail adding svn user to manager because exist !");
    }
    //send mail
    if(isset($result->email)) {
        //login path
        $login_url = rtrim(hw_valid_url($cp->domain), '/'). '/'. ltrim($login_path, '/');

        $body = HW_Twig_engine::twig_render('email/create_wp_user.tpl', array(
            'domain' => $cp->domain,'username' => $cp->cpaneluser,'email_domain' => $cp->email_domain,
            'user' => $result->user, 'pass' => $result->pass, 'email' => $result->email,
            'login_url' => $login_url
        ));
        send_mail1($result->email, 'Hoangweb.COM - tạo tài khoản đăng nhập Wordpress', $body);
    }
}
add_action('ajax_task_create_wp_user', 'create_wp_user');
/**
 * delete wp user
 * @param $cp
 */
function del_wpuser($cp) {
    $subdomain = _post('subdomain');
    $user_id = _post('user_id');
    $update_svn_user = _post('update_svn_user');    //also want to del svn user

    //upload wp tool
    $res = $cp->upload_wptool($cp->acc_id, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=del_user&acc='.$cp->acc_id;
    $data = array('user_id' => $user_id ,'update_svn_user' => $update_svn_user);
    $res = curl_post($url, $data);
    ajax_output($res);
}
add_action('ajax_task_del_wpuser', 'del_wpuser');

/**
 * update wp user role
 * @param $cp
 */
function update_wpuser_role($cp) {
    $subdomain = _post('subdomain');
    $role = _post('role');
    $user_id = _post('user_id');

    //upload wp tool
    $res = $cp->upload_wptool($cp->acc_id, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=update_user_role&acc='.$cp->acc_id;
    $data = array('user_id' => $user_id ,'role' => $role);
    $res = curl_post($url, $data);
    ajax_output($res);
}
add_action('ajax_task_update_wpuser_role', 'update_wpuser_role');

/**
 * update wp-config.php
 * @param $cp
 */
function update_wpconfig($cp) {
    $subdomain = _post('subdomain');
    $dbid = _post('dbid');
    $cfg_dir = '/public_html/'.$subdomain;
    $cfg_file = 'wp-config.php';

    //get db user info
    $db = get_acct_dbs($cp->acc_id, ' and dbuser.id='.$dbid)->FetchRow();
    if(!$db) exit('DB User not found.');
    //valid
    $db['db'] = $cp->cpaneluser .'_'.$db['db'];
    $db['dbuser'] = $cp->cpaneluser .'_'.$db['dbuser'];
    $db['dbpass'] = decrypt($db['dbpass']);

    $cpanel_file = $cp->get_instance('fileman');
    $res = $cpanel_file->get_filecontent($cfg_file, $cfg_dir);
    $result = json_decode($res);

    if(isset($result->data->result)) {
        $content = ($result->data->result);
        if(empty($content)) {
            $content = file_get_contents(TMP_DIR.'/wp-config.php');
        }
        if(empty($content)) exit("Not found wp-config.php on server or from tmp/ path");

        //find DB_NAME
        preg_match('#.+DB_NAME.+#',$content,$re);
        if(count($re)) {
            $content = str_replace($re[0], "define('DB_NAME', '{$db['db']}');", $content);
        }
        //find DB_USER
        preg_match('#.+DB_USER.+#',$content,$re);
        if(count($re)) {
            $content = str_replace($re[0], "define('DB_USER', '{$db['dbuser']}');", $content);
        }
        //find DB_PASSWORD
        preg_match('#.+DB_PASSWORD.+#',$content,$re);
        if(count($re)) {
            $content = str_replace($re[0], "define('DB_PASSWORD', '{$db['dbpass']}');", $content);
        }

        //save back to file
        $res = $cpanel_file->savefile(array(
            'dir'           => $cfg_dir,
            'file'          => $cfg_file,
            'content' => $content
        ));
        ajax_output($res);
    }
    #ajax_output($res);
}
add_action('ajax_task_update_wpconfig', 'update_wpconfig');

/**
 * list all wp users
 * @param $cp
 */
function list_wp_user($cp) {
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    #ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=list_users&acc='.$cp->acc_id;
    $res = curl_post($url, array('subdomain'=> $subdomain));
    ajax_output($res);

    function hw_output_table_data_cb($name, $value) {
        if($name =='svn_pass') return decrypt($value);
        return $value;
    }
    //compare to `svn_wp_users` table.
    echo '<h2>All WP Users on localhost</h2>';

    $svn_users = hw_svn_list_users($cp->domain);
    hw_output_table_data ($svn_users, array(
        'id' => 'ID',
        'svn_fullname' => "Fullname",
        'svn_user' => 'User',
        'svn_pass' => 'Pass',
        'svn_email' => 'Email',
        'domain' => 'Domain'
    ), 'hw_output_table_data_cb');
}
add_action('ajax_task_list_wp_user', 'list_wp_user');
/**
 * list all wp roles
 * @param $cp
 */
function list_wp_user_roles($cp) {
    //upload wp tool
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=list_users_roles&acc='.$cp->acc_id;
    $res = curl_post($url, array('subdomain'=> $subdomain));
    ajax_output($res);
}
add_action('ajax_task_list_wp_user_roles', 'list_wp_user_roles');
/**
 * enable wp debug
 * @param $cp
 */
function enable_wpdebug($cp) {
    $subdomain = _post('subdomain');
    $cfg_dir = '/public_html/'.$subdomain;
    $cfg_file = 'wp-config.php';

    $cpanel_file = $cp->get_instance('fileman');
    $res = $cpanel_file->get_filecontent($cfg_file, $cfg_dir);
    $result = json_decode($res);

    if(isset($result->data->result)) {
        $content = ($result->data->result);
        //find DB_NAME
        preg_match('#.+WP_DEBUG.+#',$content,$re);
        if(count($re)) {
            $content = str_replace($re[0], "define('WP_DEBUG', true);", $content);
        }

        //save back to file
        $res = $cpanel_file->savefile(array(
            'dir'           => $cfg_dir,
            'file'          => $cfg_file,
            'content' => $content
        ));
        ajax_output($res);
    }
    else echo 'Error to open wp-config.php';
}
add_action('ajax_task_enable_wpdebug', 'enable_wpdebug');
/**
 * deactive plugins
 * @param $cp
 */
function wp_deactive_plugins($cp) {
    //upload hw tool for wp
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //rm_plugins
    $data = array(
        'rm_plugins'=> _post('rm_plugins')
    );

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=deactive_plugins&acc='.$cp->acc_id;
    if(_get('rm_all')) $url .= '&rm_all=1';

    $res = curl_post($url, $data);
    ajax_output($data, 1);
    ajax_output($res);
}
add_action('ajax_task_wp_deactive_plugins', 'wp_deactive_plugins');
/**
 * active wp plugins
 * @param $cp
 */
function wp_active_plugins($cp) {
    //upload hw tool for wp
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //addlist
    $data = array(
        'addlist'=> _post('addlist')
    );

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=active_plugins&acc='.$cp->acc_id;
    $res = curl_post($url, $data);
    ajax_output($data,1);
    ajax_output($res);
}
add_action('ajax_task_wp_active_plugins', 'wp_active_plugins');

/**
 * WP list actived plugins
 * @param $cp
 */
function wp_list_plugins($cp) {
    //upload hw tool for wp
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=list_activation_plugins&acc='.$cp->acc_id;
    $res = curl_get($url);
    ajax_output($res);
}
add_action('ajax_task_wp_list_plugins', 'wp_list_plugins');
/**
 * save order of plugins
 * @param $cp
 */
function saveorder_wpplugins($cp) {
    //upload hw tool for wp
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=update_order_plugins&acc='.$cp->acc_id;
    $res = curl_get($url);
    ajax_output($res);
}
add_action('ajax_task_saveorder_wpplugins', 'saveorder_wpplugins');
/**
 * export sql file from db on target wordpress site
 * @param $cp
 */
function export_wp_db ($cp) {
    //upload hw tool for wp
    $result = array();
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    $result[] = (array)json_decode($res);
    #ajax_output($res);

    //upload 'dumper.php'
    $res = $cp->upload_wptool(0, $subdomain, 'dumper.php');
    $result[] = (array)json_decode($res) ;
    #ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=export_sql&acc='.$cp->acc_id;
    $res = curl_post($url, array('file' => _post('file')) );
    $result[] = (array) json_decode($res) ;
    #ajax_output($res);
    ajax_output($result) ;
}
add_action('ajax_task_export_db', 'export_wp_db');

/**
 * import sql to db
 * @param $cp
 */
function import_db($cp) {
    $file = _post('file');
    if($file) {
        $subdomain = _post('subdomain');

        //upload .sql or sql compress file
        $result = array();
        $res = $cp->upload_wptool(0, $subdomain, $file);
        $result[] = (array)json_decode($res);

        //upload hw tool for wp
        $res = $cp->upload_wptool(0, $subdomain);
        $result[] = (array)json_decode($res);

        //run this file
        $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=import_sql&acc='.$cp->acc_id;
        $res = curl_post($url, array('file' => basename($file), 'dbname' => _post('dbname')) );
        $result[] = (array) json_decode($res) ;

        ajax_output($result);
    }
}
add_action('ajax_task_import_db', 'import_db');
/**
 * install wp on the acct
 * @param $cp
 */
function install_wp($cp) {
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_uploader/&&__run_all_in_one.bat '.$cp->acc_id.'"');
    ajax_output($str);
}
add_action('ajax_task_install_wp', 'install_wp');

/**
 * turn wp into maintenance mode
 * @param $cp
 */
function wp_maintenance($cp) {
    $subdomain = _post('subdomain');    //subdomain
    $_url = _post('url', 'http://hoangweb.com');
    $unlock = _post('unlock', 0);

    /*
    //method 1: not recommended
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool($acc_id, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim($domain, '/') . '/'.HW_WP_TOOL_FILE.'?do=maintenance_mode&acc='.$acc_id;
    $data = array('redirect_url' => $_url, 'unlock' => $unlock);
    $res = curl_post($url, $data);
    ajax_output($res);
    */
    $root_dir = '/public_html/'.$subdomain;
    $htaccess_file = '.htaccess';

    $cpanel_file = $cp->get_instance('fileman');
    //method 1: you need .maintenance file place at root
    $cpanel_file->uploadfiles(TMP_DIR.'/.maintenance', $root_dir);

    //method 2: modify .htaccess
    $res = $cpanel_file->get_filecontent($htaccess_file, $root_dir);
    $result = json_decode($res);

    if(isset($result->data->result)) {
        $htaccess = ($result->data->result);

        //unlock site
        if($unlock) {
            $items=preg_split('#(\# BEGIN HOANGWEB LOCKSITE)|(\# END HOANGWEB LOCKSITE)#', $htaccess,NULL,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            if(count($items)) {
                $found=0;
                $result = array();
                foreach ($items as $line) {
                    if(!(trim($line))) continue;
                    if(strpos($line, '# BEGIN HOANGWEB LOCKSITE')!==false) {
                        $found=1;
                        continue;
                    }
                    if($found && strpos($line, '# END HOANGWEB LOCKSITE')!==false) {
                        $found=0;
                        continue;
                    }
                    if(!$found) $result[] = $line;
                }
                $htaccess = (join(PHP_EOL, $result));

            }
        }
        //lock site
        else {
            //block code
            $append="# BEGIN HOANGWEB LOCKSITE\nErrorDocument 403 $_url
Order deny,allow
Deny from all\n# END HOANGWEB LOCKSITE";

            $htaccess .= "\n\n{$append}";
        }

        //save back to file
        $res = $cpanel_file->savefile(array(
            'dir'           => $root_dir,
            'file'          => $htaccess_file ,
            'content' => $htaccess
        ));
        ajax_output($res);
    }
}
add_action('ajax_task_wp_maintenance', 'wp_maintenance');
/**
 * run sql to fixed url base in wordpress that stored in wp_posts, wp_postmeta,wp_options
 * @param $cp
 */
function wp_sql_fixedbaseurl($cp) {
    $db = _post('db');
    $dbuser = _post('dbuser');
    $where ='';
    if($db) $where .=" and db='{$db}' ";
    if($dbuser) $where .=" and dbuser='{$dbuser}' ";

    $dbs = get_acct_dbs($cp->acc_id, $where);
    if($dbs) {
        $dbs = $dbs->FetchRow();
    }
    if(is_array($dbs) && count($dbs)) {
        $cmd= _post('cmd'); //contain command file
        $find = _post('findstr');   //what to find?
        $repl = _post('replacestr');    //replace with ?
        $save = WHM_CPANEL_SHELL_APP."/x_ssh/commands";

        //modify cmd file, do not put -D param before dbname
        //https://my.bluehost.com/cgi/help/112
        $txt ="mysql -p -u {$cp->cpaneluser}_{$dbs['dbuser']} {$cp->cpaneluser}_{$dbs['db']} < wp_fixedbaseurl_mysql.sql
read -n1 -r -p 'Press any key to continue...' key";

        file_put_contents($save.'/'.$cmd, $txt);

        //create sql file
        $sql = 'update wp_options set option_value=REPLACE(option_value,"'.$find.'","'.$repl.'");'.PHP_EOL;
        $sql .= 'update wp_posts set post_content=REPLACE(post_content,"'.$find.'","'.$repl.'");'.PHP_EOL;
        $sql .= 'update wp_posts set guid=REPLACE(guid,"'.$find.'","'.$repl.'");'.PHP_EOL;
        $sql .= 'update wp_postmeta set meta_value=REPLACE(meta_value,"'.$find.'","'.$repl.'");'.PHP_EOL;
        file_put_contents($save.'/wp_fixedbaseurl_mysql.sql', $sql);

        //upload .sql file to server
        $cpanel_file = $cp->get_instance('fileman_no_auth');
        $res = $cpanel_file->uploadfiles(array(
                'fullpath' => "{$save}/wp_fixedbaseurl_mysql.sql",
                'type' => '',
                'name' => 'wp_fixedbaseurl_mysql.sql'
            ),
            '/');
        ajax_output($res);

        //run batch file
        $cp->run_command();
    }
}
add_action('ajax_task_wp_sql_fixedbaseurl', 'wp_sql_fixedbaseurl');

/**do plugins configuration
 * @param $cp
 */
function hw_wp_import_plugins_setting($cp) {
    $cmd= _post('cmd'); //contain command file
    //$commands_path = WHM_CPANEL_SHELL_APP."/x_ssh/commands";

    //run batch file
    //if($cmd) $cp->run_command_as_root();
    if($cmd) $cp->run_command();    //should do in account context
}
add_action('ajax_task_wp_import_plugins_setting', 'hw_wp_import_plugins_setting') ;
