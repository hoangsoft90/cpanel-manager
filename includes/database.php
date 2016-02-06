<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 16:30
 */
//-------------------------------------------------DB--------------------------------------------------------
/**
 * add account database
 * @param $data
 * @param string $acc_id
 * @return mixed
 */
function add_acct_db($data) {
    global $DB;
    $acc_id = isset($data['cpid']) ? $data['cpid'] : '';
    $db = isset($data['db']) ? $data['db'] : '';
    $dbuser = isset($data['dbuser']) ? $data['dbuser'] : '';
    $dbpass = isset($data['dbpass']) ? $data['dbpass'] : '';

    $insert_cols = '';
    $insert_vals = '';
    foreach($data as $col => $val) {
        if($val === '') continue;
        $insert_cols .= $col.',';
        $insert_vals .= "'{$val}',";
    }
    $insert_cols = trim($insert_cols,',') ;
    $insert_vals = trim($insert_vals,',') ;

    //first to add db for acct
    $sql = "INSERT INTO `cpanel-dbusers` ($insert_cols)
SELECT * FROM (SELECT $insert_vals) AS tmp
WHERE NOT EXISTS (
    SELECT * FROM `cpanel-dbusers` WHERE cpid = '{$acc_id}') LIMIT 1;";
    $DB->Execute($sql);

    //for new db
    if(!$DB->Affected_Rows()) {
        $sql= "INSERT INTO `cpanel-dbusers` ($insert_cols)
SELECT * FROM (SELECT $insert_vals) AS tmp
WHERE NOT EXISTS (
    SELECT * FROM `cpanel-dbusers` WHERE cpid = '{$acc_id}' and db='{$db}') LIMIT 1;";
        $DB->Execute($sql);
    }
    //for new dbuser
    if(!$DB->Affected_Rows()) {
        $sql= "INSERT INTO `cpanel-dbusers` ($insert_cols)
SELECT * FROM (SELECT $insert_vals) AS tmp
WHERE NOT EXISTS (
    SELECT * FROM `cpanel-dbusers` WHERE cpid = '{$acc_id}' and db='{$db}' and dbuser='{$dbuser}') LIMIT 1;";
        $DB->Execute($sql);
    }
    //update sql
    if(!$DB->Affected_Rows()) {
        //update db user pass
        $sql = "UPDATE `cpanel-dbusers` set dbpass=? where cpid=? and db=? and dbuser=?";
        $result =$DB->Execute($sql, array($dbpass,$acc_id, $db,$dbuser ) );
    }

    return $DB->Affected_Rows();
}

/**
 * delete db from acct
 * @param $data
 */
function del_acct_db($data) {
    global $DB;
    $fields = '';
    $vals = array();

    foreach($data as $field => $val) {
        if($val === '') continue;
        $fields .= $field.'=? and ';
        $vals[] = $val;
    }
    //valid
    $fields = trim($fields, ' and ');

    $sql = "delete from `cpanel-dbusers` where $fields";
    $DB->Execute($sql, $vals);
    return $DB->Affected_Rows();
}

/**
 * list all dbs from database
 * @param $acc_id given acct id in `cpanel-accts` table
 * @param $where addition where clause
 */
function get_acct_dbs($acc_id='', $where='') {
    global $DB;
    $sql = 'select dbuser.id as autoID,dbuser.db,dbuser.dbuser,dbpass,acct.* from `cpanel-dbusers`as dbuser LEFT join `cpanel-accts` as acct on dbuser.cpid=acct.id';
    if($acc_id) $sql .= ' where acct.id="'.$acc_id.'" ';
    if($where) $sql .= $where;

    return $DB->Execute($sql);
}

/**
 * list acc databases
 * @param $data
 */
function listacc_dbs($data) {
    $cpanel_mysql = $data->get_instance('mysql');
    /*$cpanel_mysql = new HW_CPanel_Mysql($cpanel->xmlapi, array(
        'user' => $cpaneluser,
        'pass' => $cpaneluser_pass,
        'email' => $email_domain
    ));*/

    $dbs = $cpanel_mysql->listdbs($data->cpaneluser);
    #var_dump($dbs);
    if($data->param('json_format')) {
        ajax_output($dbs);
    }
    else {
        echo 'Account: <b>'.$data->cpaneluser.'</b><br/>';
        foreach($dbs as $name) {
            $del_link = '(<a href="javascript:void(0)" onclick="hw_deldb(this,\''.$data->acc_id.'\',\''.$data->cpaneluser.'_'.$name.'\')">Del</a>)';
            $checkdb_link = '(<a href="javascript:void(0)" onclick="hw_checkdb(this,\''.$data->acc_id.'\',\''.$data->cpaneluser.'_'.$name.'\')">Check DB</a>)';
            echo "<div>{$name} - {$del_link}{$checkdb_link}</div>";
        }
        #ajax_output($dbs);
    }
}
add_action('ajax_task_listacc_dbs', 'listacc_dbs');

/**
 * list mysql table data
 * @param $data
 */
function listacc_dbtable_data($data) {
    $table = _post('table');
    $subdomain = _post('subdomain');
    $res = $data->upload_wptool($data->acc_id, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($data->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=list_dbtable_data&acc='.$data->acc_id;
    $data = array('table' => $table );
    $res = curl_post($url, $data);
    ajax_output($res);
}
add_action('ajax_task_listacc_dbtable_data', 'listacc_dbtable_data');

/**
 * list saved acc dbs
 * @param $data
 */
function listsaved_acc_dbs($data) {
    $list_dbs = get_acct_dbs($data->acc_id);
    echo '<table border="1px" cellpadding="3px" cellspacing="1px" class="hover-table">
            <tr>
                <td><strong>ID</strong></td>
                <td><strong>CPID</strong></td>
                <td><strong>cpanel domain</strong></td>
                <td><strong>cpanel user</strong></td>
                <td><strong>cpanel email</strong></td>

                <td><strong>DB</strong></td>
                <td><strong>DB User</strong></td>
                <td><strong>DB Pass</strong></td>
                <td></td>
            </tr>';
    while($row = $list_dbs->FetchRow()) {
        echo '<tr>';
        echo "<td><strong>{$row['autoID']}</strong></td>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['cpanel_domain']}</td>";
        echo "<td>{$row['cpanel_user']}</td>";
        echo "<td>{$row['cpanel_email']}</td>";

        echo "<td>{$row['db']}</td>";
        echo "<td>{$row['dbuser']}</td>";
        echo "<td>".decrypt($row['dbpass'])."</td>";
        echo '<td><a href="javascript:void(0)" onclick="hw_deldb_fromlocal('.$row["id"].');">Del from db</a></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<div>Chú ý: Nhập chỉ số ID trong tool cài đặt WP tự động để cấu hình wp-config.php</div>';
}
add_action('ajax_task_listsaved_acc_dbs', 'listsaved_acc_dbs');
/**
 * list users from dbs acc
 * @param $data
 */
function listacc_users($data) {
    $cpanel_user = $data->get_instance('user');
    /*$cpanel_user = new HW_CPanel_User($cpanel->xmlapi, array(
        'user' => $cpaneluser,
        'pass' => $cpaneluser_pass,
        'email' => $email_domain
    ));*/
    $users = $cpanel_user->listUsers($data->cpaneluser);
    #var_dump($json_format);
    if($data->param('json_format')) {
        ajax_output($users);
    }
    else {
        echo 'Account: <b>'.$data->cpaneluser.'</b><br/>';
        foreach($users as $user) {
            $del_link = '(<a href="javascript:void(0)" onclick="hw_deluser(this,\''.$data->acc_id.'\',\''.$data->cpaneluser.'_'.$user.'\')">Del</a>)';
            $deluserdb_link = '(<a href="javascript:void(0)" onclick="hw_deluserdb(this,\''.$data->acc_id.'\',\''.$data->cpaneluser.'_'.$user.'\',\'\')">Del userdb</a>)';
            $setpass_link = '(<a href="javascript:void(0)" class="disable-link" onclick="hw_setuserpass(this,\''.$data->acc_id.'\',\''.$data->cpaneluser.'_'.$user.'\')">Set Pass</a>)';

            echo "<div>{$user} - {$del_link}{$deluserdb_link}{$setpass_link}</div>";
        }
    }
}
add_action('ajax_task_listacc_users', 'listacc_users');

/**
 * delete db
 * @param $data
 */
function deldb($data) {
    $cpanel_mysql = $data->get_instance('mysql');
    $out = $cpanel_mysql->deldb(_get('db'), $data->cpaneluser);
    //del from database
    del_acct_db(array('cpid'=>$data->acc_id, 'db'=> str_replace($data->cpaneluser. '_','', _get('db')) ));

    ajax_output($out);
}
add_action('ajax_task_deldb', 'deldb');

/**
 * check db
 * @param $data
 */
function checkdb($data) {
    $cpanel_mysql = $data->get_instance('mysql');
    $out = $cpanel_mysql->checkdb(_get('db'), $data->cpaneluser);
    ajax_output($out);
}
add_action('ajax_task_checkdb', 'checkdb');

/**
 * delete db user
 * @param $cp
 */
function deluser($cp) {
    $cpanel_user = $cp->get_instance('user');
    $out = $cpanel_user->deluser(_get('user'), $this->cpaneluser);
    //delete from database (manager)
    del_acct_db(array(
        'cpid'=>$this->acc_id,
        'dbuser' => str_replace($this->cpaneluser. '_','', _get('user'))
    ));

    ajax_output($out);
}
add_action('ajax_task_deluser', 'deluser');
/**
 * set user pass
 * @param $cp
 */
function setuserpass($cp) {
    #$cpanel_user = HW_CPanel_User::loadacct($acc_id );
    $cpanel_user = $cp->get_instance('user');
    $out = $cpanel_user->setuserPass(_post('user'),_post('pass'));
    ajax_output($out);
}
add_action('ajax_task_setuserpass', 'setuserpass');
/**
 * delete user db
 * @param $cp
 */
function deluserdb($cp) {
    $cpanel_mysql = $this->get_instance('mysql');//new HW_CPanel_Mysql($host, $cpaneluser, $cpaneluser_pass);
    $cpanel_mysql->deluserdb(_get('user'), _get('db'));
}
add_action('ajax_task_deluserdb', 'deluserdb');
/**
 * delete db from manager
 * @param $cp
 */
function deldb_from_manager($cp) {
    if(is_numeric(_get('id') ) ) {
        del_acct_db(array('id' => _get('id')));
        echo ('delete db='._get('id') );
    }
}
add_action('ajax_task_deldb_from_manager', 'deldb_from_manager');
/**
 * export db from localhost used by this application
 * @param $cp
 */
function export_localdb($cp) {
    include ('libs/dumper.php');
    $name = _post('file', DB_NAME);
    $include_tables = _post('include_tables');
    $savepath = _post('savepath', 'tmp/');

    try {
        $arg = array(
            'host' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS,
            'db_name' => DB_NAME,
        );
        if($include_tables){
            $arg['include_tables'] = preg_split('#[,\s]+#', $include_tables);
        }
        $world_dumper = Shuttle_Dumper::create($arg);

        // dump the database to gzipped file
        #$world_dumper->dump($savepath.$name. '.sql.gz');   //.sql.gz not allow to import by mysql command

        // dump the database to plain text file
        $world_dumper->dump($savepath.$name. '.sql');
        echo 'Export database '.DB_NAME.' successfull !';
    }
    catch(Shuttle_Exception $e) {
        ajax_output(array('result' => "Couldn't dump database: " . $e->getMessage() )) ;
    }
}
add_action('ajax_task_export_localdb', 'export_localdb');