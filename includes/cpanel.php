<?php
//---------------------------------------------------cPanel------------------------------------------------------
/**
 * get domain host
 * @param $domain
 * @param string $subdomain
 * @return string
 */
function _domain($domain, $subdomain ='') {
    return trim($subdomain, '/')? trim($subdomain, '/').'.'. $domain : $domain;
}
/**
 * list accts
 */
function list_whm_accts() {
    global $DB;
    $result = array();
    $accts= $DB->Execute("select id,cpanel_user,cpanel_pass,cpanel_host,cpanel_domain,cpanel_email,token,expires from `cpanel-accts` as t1 left join `cpanel-tokens` as t2 on t1.id=t2.cpid");
    while($row=$accts->FetchRow()) {
        $result[] = $row;
    }
    return $result;
}

/**
 * get acc by id
 * @param $id
 * @return mixed
 */
function get_cpanel_acc($id) {
    #static $result;        //don;t execute one time
    global $DB;
    #if(empty($result)) {
        $sql= "select id,cpanel_user,cpanel_pass,cpanel_host,cpanel_domain,cpanel_email,token,expires,ssh_key,ssh_key_pass from `cpanel-accts` as t1 left join `cpanel-tokens` as t2 on t1.id=t2.cpid where id='{$id}'";
        $result = $DB->Execute($sql)->FetchRow();
    #}
    return $result ;
}

/**
 * @param array $arg
 */
function get_cpanel_info($arg = array()) {
    global $DB;
    $where = '';
    $where_vals = array();

    foreach ($arg as $key=>$val) {
        $where .= $key.'=? and ';
        $where_vals[] = $val;
    }
    //valid
    $where=rtrim($where, ' and ');

    $sql = 'select * from `cpanel-accts` where '. $where;
    $rs = $DB->Execute($sql, $where_vals);
    if($rs) return $rs->FetchRow();
}


/**
 * update cpanel user token
 * @param $id
 * @param $data
 */
function update_cpacct_token($id, $data = array() ) {
    global $DB;
    $token = $data['token'];
    $expires = $data['expires'];

    //update exists cpanel id
    $DB->Execute("UPDATE `cpanel-tokens` set token=?, expires=? where cpid=?", array($token,$expires, $id));
    if(!$DB->Affected_Rows()) { //add new one
        $sql = "INSERT INTO `cpanel-tokens` (cpid, token,expires) values ("
            . $id . ','
            . $DB->qstr($token) .','
            . $DB->qstr($expires) .')';

        $DB->Execute($sql);
        return $DB->Affected_Rows();
    }

    /*if(count($DB->Execute('select * from `cpanel-tokens` where id="'.$id.'"')->FetchRow()) ) {

    }*/
}

/**
 * update acpanel account
 * @param $data
 * @param string $acc_id
 * @return mixed
 */
function update_cpacct($data, $acc_id='') {
    global $DB;
    /*$user = isset($data['user']) ? $data['user'] : '';
    $pass = isset($data['pass']) ? $data['pass'] : '';
    $host = isset($data['host']) ? $data['host'] : '';
    $domain = isset($data['domain']) ? $data['domain'] : '';
    $email = isset($data['email']) ? $data['email'] : '';
*/
    //prepare fields
    $insert_keys = '';
    $insert_vals = '';
    $update_keys = '';
    $update_vals = array();
    foreach($data as $key => $val) {
        $insert_keys .= ($key.',');
        $insert_vals .= ($DB->qstr($val). ',');

        $update_keys .= ($key.'=? ,');
        $update_vals[] = $val;
    }
    //pure
    $update_keys = trim($update_keys,' ,');
    $insert_keys = trim($insert_keys,',');
    $insert_vals = trim($insert_vals,',');

    //update acc
    if(is_numeric($acc_id)) {
        if($update_keys) {
            $update_vals[]= $acc_id;
            $sql = "UPDATE `cpanel-accts` set $update_keys where id=?";
            $result= $DB->Execute($sql, $update_vals);
        }

    }
    else{
        //add row
        /*$sql = "INSERT INTO `cpanel-accts` ($insert_keys) values ("
            . $DB->qstr($user) . ','
            . $DB->qstr($pass) .','
            . $DB->qstr($host).","
            . $DB->qstr($domain).","
            . $DB->qstr($email).")";*/
        if($insert_keys) {
            $sql = "INSERT INTO `cpanel-accts` ($insert_keys) values ($insert_vals)";
            $result= $DB->Execute($sql);
        }

    }
    return $result;
}
/**
 * delete acc
 * @param $id
 * @return mixed
 */
function del_cpanel_acc($id) {
    global $DB;
    $sql = "delete from `cpanel-accts`";
    if(is_array($id)) {
        $where_keys='';
        $where_vals = array();
        foreach($id as $key => $val) {
            $where_keys .= ($key.'=? and ');
            $where_vals[] = $val;
        }
        $where_keys = trim($where_keys, ' and ');
        $DB->Execute("$sql where $where_keys", $where_vals);
    }
    else {
        $sql.= " where id='$id'";
        $DB->Execute($sql);
    }
    return $DB->Affected_Rows();
}

//-----------------------------------------------Authorize----------------------------------------------------------
/**
 * authorize cpanel
 * @param $acc
 */
function authorize_cpanel($acc) {
    //get cpanel credentials
    /*$acc = get_cpanel_acc($acc);
    $host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
    $cpaneluser = isset($acc['cpanel_user'])? $acc['cpanel_user']:"";
    $cpaneluser_pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :'';
    $email_domain= isset($acc['cpanel_email'])? $acc['cpanel_email']:'laptrinhweb123@gmail.com';

    $cpanel = new HW_CPanel($host, $cpaneluser, $cpaneluser_pass);*/
    $cpanel = HW_CPanel::loadacct_instance($acc, true);
    return $cpanel;
}

/**
 * authorize root
 * @return HW_CPanel object
 */
function authorize_hwm() {
    //accesshash
    $code = read_file('db/snippet.hw');
    return HW_CPanel::authorize_root(HW_WHM_IP, $code);
}

#--------------------------------------------WP tools-------------------------------------------------

/**
 * list users from any wordpress site
 * @param $acc_id
 * @param string $subdomain
 * @param int $output_json (0|1)
 */
function hw_wptool_list_users($acc_id, $output_json=0,$subdomain ='') {
    $acc = get_cpanel_acc($acc_id);
    $domain = isset($acc['cpanel_domain'])? $acc['cpanel_domain']: '';
    if(empty($domain)) {
        return ;
    }
    //update wp tool
    $res = hw_upload_wptool($acc_id, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim($domain, '/') . '/'.HW_WP_TOOL_FILE.'?do=list_users&json=1&acc='.$acc_id;
    $res = curl_post($url, array('subdomain'=> $subdomain));

    return (array)json_decode($res);
}


/**
 * list all cpanel accts
 * @param $data
 */
function _listcp_accts ($data) {
    global $DB;
    $cpanel = authorize_hwm();
    $accts = $cpanel->listAccts();
    $accts = json_decode($accts);
    $sql = '';

    echo '<table border="1px" cellpadding="4" cellspacing="1" class="hover-table">';
    echo '<tr>
        <!-- <td>IP</td> -->
        <td><strong>domain</strong></td>
        <td><strong>user</strong></td>
        <td><strong>email</strong></td>
        <td><strong>diskused</strong></td>
        <td><strong>disklimit</strong></td>
        <td><strong>maxaddons</strong></td>
        <td><strong>maxftp</strong></td>
        <td><strong>maxlst</strong></td>
        <td><strong>maxparked</strong></td>
        <td><strong>maxpop</strong></td>
        <td><strong>maxsql</strong></td>
        <td><strong>maxsub</strong></td>

        <td>X</td>

        </tr>';
    if($accts->status == '1') {
        foreach($accts->acct as $row) {
            if(_get('update_list')) {
                $sql .= "
                INSERT INTO `cpanel-accts` (cpanel_user,cpanel_host,cpanel_domain,cpanel_email)
                SELECT * FROM (SELECT {$DB->qstr($row->user)},{$DB->qstr($row->ip)},{$DB->qstr($row->domain)},{$DB->qstr($row->email)}) AS tmp
                WHERE NOT EXISTS (
                    SELECT * FROM `cpanel-accts` WHERE cpanel_user = '{$row->user}' and cpanel_host ='{$row->ip}'
                ) ;";
            }


            echo '<tr>';
            #echo '<td>'.$row->ip. '</td>';
            echo '<td class="domain"><a href="http://'.$row->domain.'" target="_blank">'.$row->domain. '</a></td>';
            echo '<td class="user">'.$row->user.'@'.$row->ip. '</td>';
            echo '<td class="email">'.$row->email. '</td>';
            echo '<td>'.$row->diskused. '</td>';
            echo '<td>'.$row->disklimit. '</td>';
            echo '<td>'.$row->maxaddons .'</td>';
            echo '<td>'.$row->maxftp .'</td>';
            echo '<td>'.$row->maxlst. '</td>';
            echo '<td>'.$row->maxparked. '</td>';
            echo '<td>'.$row->maxpop. '</td>';
            echo '<td>'.$row->maxsql. '</td>';
            echo '<td>'.$row->maxsub. '</td>';

            echo '<td><a href="javascript:void(0)" data-domain="'.$row->domain.'" data-host="'.$row->ip.'" onclick="hw_cp_del(this,\''.$row->user.'\')">Delete</a> |
                <a href="javascript:void(0)" onclick="hw_cp_setpass(this,\''.$row->user.'\')">Set pass</a> |
               <a href="javascript:void(0)" data-ip="'.$row->ip.'" data-user="'.$row->user.'" data-email="'.$row->email.'" data-domain="'.$row->domain.'" onclick="hw_save_cpacct(this)">Save</a>
            </td>';
            echo '</tr>';
        }

    }

    echo '</table>';
    //update cpanel accts list
    if(!empty($sql)) {
        global $DB;
        #print_r($sql);
        $DB->Execute($sql);
    }
}
add_action('ajax_task_listcp_accts', '_listcp_accts');
/**
 * delete acct
 * @param $data
 */
function delacct($data) {
    $cpanel = authorize_hwm();
    $rs = $cpanel->delAcct(_get('cpuser'));
    ajax_output($rs);

    //del from manager
    $res = del_cpanel_acc(array(
        'cpanel_user'=>_get('cpuser'),
        'cpanel_host' => _post('cp_host'),
        'cpanel_domain' => _post('cp_domain')
    ));
    ajax_output($res);

    //alert email
    $body = HW_Twig_engine::twig_render('email/delacct.tpl', array(
        'domain'=>$data->domain,'username'=>$data->cpaneluser,'email_domain'=>$data->email_domain
    ));
    send_mail1($data->email_domain, 'Hoangweb - Hủy tài khoản hosting',$body);
}
add_action('ajax_task_delacct', 'delacct');

/**
 *  set account pass
 * @param $data
 */
function setacctpass($data) {
    $cpuser = _get('cpuser');
    $cppass = _post('pass');

    $cpanel = authorize_hwm();
    $res = $cpanel->setAcctPass($cpuser, $cppass);
    $result = json_decode($res);
    if( isset($result->passwd) && isset($result->passwd[0]->statusmsg)
        && strpos($result->passwd[0]->statusmsg,'Password changed')!== false)
    {
        $row = get_cpanel_info(array('cpanel_user' => $cpuser, 'cpanel_host' => $cpanel->host));
        $cpid = isset($row['id'])? $row['id'] : '';

        update_cpacct(array('cpanel_user' => $cpuser, 'cpanel_pass' => encrypt($cppass), 'cpanel_host'=> $cpanel->host), $cpid);
    }
    //print result
    ajax_output($res);
    //send mail
    $body = HW_Twig_engine::twig_render('email/setacctpass.tpl', array(
        'domain'=>$data->domain,'username'=>$data->cpaneluser,'email_domain'=>$data->email_domain,
        'cpanel_pass' => $cppass, 'cpanel_host' => $cpanel->host
    ));
    send_mail1($data->email_domain, 'Hoangweb - thay đổi tài khoản cpanel', $body);
}
add_action('ajax_task_setacctpass', 'setacctpass');

/**
 * save cpanel info to db
 * @param $data
 */
function saveacct($data) {
    $ip = _post('ip');
    $domain = _post('domain');
    $cpuser = _post('user');
    $email = _post('email');

    $row = get_cpanel_info(array('cpanel_user' => $cpuser, 'cpanel_host' => $ip ));
    $cpid = isset($row['id'])? $row['id'] : '';
    //update acct to db
    update_cpacct(array('cpanel_user' => $cpuser,  'cpanel_host'=> $ip, 'cpanel_domain'=>$domain, 'cpanel_email' => $email), $cpid);
}
add_action('ajax_task_saveacct', 'saveacct');
/**
 * test cpanel acct
 * @param $data
 */
function testacct($data) {
    $cpanel = HW_CPanel::loadacct_instance(_get('id'), false);
    if($cpanel) {
        $res = $cpanel->get_authorize(true);
        ajax_output($res);
    }
    else echo 'missing credentials';
}
add_action('ajax_task_testacct', 'testacct');

/**
 * create user acct token
 * @param $data
 */
function create_cpuser_token($data) {
    $cpanel = new HW_CPanel();
    $res = $cpanel->create_cpuser_session(_get('id'));
    ajax_output($res) ;
}
add_action('ajax_task_create_cpuser_token', 'create_cpuser_token');
/**
 * modify account
 * @param $data
 */
function modifyacct($data) {
    #$cpanel = authorize_hwm();
    $cpanel = $data->get_instance('cpanel');
    $acct = array() ;
    $cfg = 'BWLIMIT,HASSHELL,hasshell,contactemail,DNS,HASCGI,HASSPF,LOCALE,MAXADDON,MAXFTP,MAXLST,MAXPARK,MAXPOP,MAXSQL,MAXSUB,QUOTA,frontpage';
    foreach (explode(',', $cfg) as $opt) {
        if( _post(strtolower($opt)) ) $acct[$opt] = _post(($opt));
    }

    //create cpanel from whm
    $result = $cpanel->modifyacct($acct);
    ajax_output($result);
}
add_action('ajax_task_modifyacct', 'modifyacct');
/**
 * generate ssh key
 * @param $data
 */
function generate_sshkey($data) {
    $result = array();
    $cpanel = $data->get_instance('cpanel');
    $cpanel_file = $data->get_instance('fileman_no_auth');
    #$cpanel_file = new HW_CPanel_Fileman($host,$cpaneluser, $cpaneluser_pass);
    if($cpanel ) {
        $key = $data->cpaneluser.'-sshkey' ;
        $keypass = generateStrongPassword(20, false,'lud'); //create safe password
        //list keys
        $keys = $cpanel->list_sshkeys();
        $keys = json_decode($keys);
        if(isset($keys->cpanelresult->data) && is_array($keys->cpanelresult->data))
            foreach($keys->cpanelresult->data as $_key) {
                if(strpos($_key->name, $key)!==false) {

                    //del putty key file
                    $putty_key = "/home/{$data->cpaneluser}/.ssh/putty/{$_key->name}.ppk";
                    $cpanel_file->delfiles($putty_key);

                    //remove ssh key file on server
                    $cpanel->del_sshkey($_key->name);    //del private key
                    $cpanel->del_sshkey($_key->name,1);  //del public key

                    /*unlink($key->file);
                    if(!empty($key->haspub) && $key->haspub) {
                        unlink($key->file.".pub");
                    }*/
                }
            }

        //generate ssh key
        $res = $cpanel->generate_sshkey($key, $keypass);

        $result[] = array('sshkey' => array('key' => $key, 'pass'=> $keypass));
        $result[] = (array)json_decode($res);

        //save ssh key to db for this account
        $data = array(
            'ssh_key' => $key,
            'ssh_key_pass' => $keypass
        );
        update_cpacct($data,$data->acc_id);

        //authorize key
        $res = $cpanel->authorizes_sshkey($key) ;
        $result[] = (array)json_decode($res);

        //convert key to ppk
        $res = $cpanel->sshkey_converttoppk($key, $keypass);
        $result[] = (array)json_decode($res);

        ajax_output($result);
    }
}
add_action('ajax_task_generate_sshkey', 'generate_sshkey');

/**
 * generate ssh keys and download them to computer using batch file
 * @param $data
 */
function generate_sshkey_batch($data) {
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__gen-sshkey.bat '.$data->acc_id.'"');
    ajax_output($str);
}
add_action('ajax_task_generate_sshkey_batch', 'generate_sshkey_batch');

/**
 * test ssh access connection
 * @param $data
 */
function test_sshconnection($data) {
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__test-ssh-cpanel.bat '.$data->acc_id.'"');
    ajax_output($str);
}
add_action('ajax_task_test_sshconnection', 'test_sshconnection');

/**
 * clear spoom exim on server this cause 500 code when reach disk space.
 * @param $data
 */
function clear_spoolexim($data) {
    $cmd= _post('cmd'); //get command file
    $ssh_batch = _post('ssh_batch');

    $data->run_command_as_root($ssh_batch , $cmd);
}
add_action('ajax_task_clear_spoolexim', 'clear_spoolexim');

/**
 * create subdomain
 * @param $cp
 */
function create_subdomain($cp) {
    $subdomain = _post('subdomain');
    $path = _post('subdomain_path');

    $cpanel = $cp->get_instance('cpanel');//HW_CPanel::loadacct_instance($acc_id, false);
    //create a subdomain
    $res = $cpanel->create_subdomain($subdomain, $path);
    ajax_output($res);
}
add_action('ajax_task_create_subdomain', 'create_subdomain');
/**
 * del a subdomain
 * @param $cp
 */
function del_subdomain($cp) {
    $subdomain = _post('subdomain');

    $cpanel = $cp->get_instance('cpanel');//HW_CPanel::loadacct_instance($acc_id, false);
    //create a subdomain
    $res = $cpanel->del_subdomain($subdomain );
    ajax_output($res);
}
add_action('ajax_task_del_subdomain', 'del_subdomain');
/**
 * list subdomains
 * @param $cp
 */
function list_subdomains($cp) {
    $cpanel = $cp->get_instance('cpanel');//HW_CPanel::loadacct_instance($acc_id, false);
    $res = $cpanel->list_subdomains();
    #ajax_output($res);
    $result = json_decode($res);

    if($result->cpanelresult->data && count($result->cpanelresult->data)) {
        echo '<table border="1px" cellpadding="3px" cellspacing="1px" class="hover-table">';
        echo '<tr>
                <td><strong>domain</strong></td>
                <td><strong>basedir</strong></td>
                <td><strong>domainkey</strong></td>
                <td></td>
            </tr>';
        foreach ($result->cpanelresult->data as $item) {
            echo '<tr>';
            echo "<td>{$item->domain}</td>";
            echo "<td>{$item->basedir}</td>";
            echo "<td>{$item->domainkey}</td>";
            echo "<td><a href='javascript:void(0)' data-subdomain='{$item->subdomain}' onclick='hw_del_subdomain(this,{$cp->acc_id})'>Delete</a></td>";
            echo '</tr>';
        }
        echo '</table>';
    }
}
add_action('ajax_task_list_subdomains', 'list_subdomains');
/**
 * open ssh terminal for root account (refer to whm)
 * @param $cp
 */
function openssh_rootaccess($cp) {
    $root_pass= HW_WHM_ROOT_PASS;
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__open-ssh-root.bat '.HW_WHM_IP.' '.$root_pass.'"');
    #$str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_uploader/&&__open-ssh-root.bat"');
    ajax_output($str);
}
add_action('ajax_task_openssh_rootaccess', 'openssh_rootaccess');