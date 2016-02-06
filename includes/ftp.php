<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 16:30
 */
//---------------------------------------------------------------------------------------------------------
#   FTP
//---------------------------------------------------------------------------------------------------------
/**
 * update ftp account for acct
 * @param $data
 * @param $acc_id
 */
function add_acctftp($data, $acc_id) {
    global $DB;
    $user = isset($data['ftp_user']) ? $data['ftp_user'] : '';
    $pass = isset($data['ftp_pass']) ? encrypt($data['ftp_pass']) : '';
    $homedir = isset($data['path']) ? encrypt($data['path']) : '';

    //update acc
    if(is_numeric($acc_id)) {
        $sql = "UPDATE `cpanel-ftp` set ftp_user=? ,ftp_pass=?, path=? where cpid=? and ftp_user=?";
        $result= $DB->Execute($sql, array($user, $pass,$homedir, $acc_id, $user));

    }
    if(!$DB->Affected_Rows()){
        //add row
        $sql = "INSERT INTO `cpanel-ftp` (ftp_user,ftp_pass,cpid) values ("
            . $DB->qstr($user) . ','
            . $DB->qstr($pass) .','
            . $DB->qstr($acc_id).")";
        $result= $DB->Execute($sql);
    }
    return $DB->Affected_Rows();
}

/**
 * get ftp for acct
 * @param $acc_id
 */
function get_acctftp($acc_id) {
    global $DB;
    $result = array();
    $rs = $DB->Execute('select * from `cpanel-ftp` where cpid=?', array($acc_id));
    if($rs)
        while($row = $rs->FetchRow()) {
            $result[$row['ftp_user']] = $row;
        }
    return $result;
}

/**
 * list acct ftp
 * @param $data
 */
function listftp_acct($data) {
    //authorize
    $cpanel_ftp = $data->get_instance('ftp');//new HW_CPanel_Ftp($data->host, $data->cpaneluser, $data->cpaneluser_pass);
    #$cpanel = authorize_hwm();

    $res = $cpanel_ftp->listftp($data->cpaneluser);
    $res = json_decode($res);
    //get saved ftp
    $ftp_data = get_acctftp($data->acc_id);

    echo '<table border="1px" cellspacing="1px" cellpadding="3px" class="hover-table">';
    echo '<tr>
        <td><strong>IP</strong></td>
        <td><strong>User</strong></td>
        <td><strong>Pass</strong></td>
        <td><strong>type</strong></td>
        <td><strong>homedir</strong></td>
        <td></td>
        <td></td>
    </tr>';
    if(isset($res->cpanelresult->data) && is_array($res->cpanelresult->data) )
        foreach($res->cpanelresult->data as $item) {
            //get ftp
            $ftp_row = (isset($ftp_data[$item->user])? $ftp_data[$item->user] : '');

            echo '<tr>';
            echo '<td>'.$data->host.'</td>';
            echo '<td>'.$item->user.'@'.$data->domain.'</td>';
            echo '<td>'.(isset($ftp_row['ftp_pass'])? decrypt($ftp_row['ftp_pass']): '').'</td>';
            echo '<td>'.$item->type.'</td>';
            echo '<td>'.$item->homedir.'</td>';
            echo '<td><a href="javascript:void(0)" onclick="hw_setftp_pass(this,\''.$data->acc_id.'\',\''.$item->user.'\')">set pass</a></td>';
            echo '<td><a href="javascript:void(0)" onclick="hw_delftp(this, \''.$data->acc_id.'\',\''.$item->user.'\')">Del</a></td>';
        }
    echo '</table>';
    #$res = $cpanel->xmlapi->api1_query($cpaneluser,'Ftp', 'list_ftp');
    #ajax_output($res);
}
add_action('ajax_task_listftp_acct', 'listftp_acct');
/**
 * list ftp sessions for the account
 * @param $data
 */
function listftp_sessions($data) {
    //authorize
    #$cpanel_ftp = new HW_CPanel_Ftp($host, $cpaneluser, $cpaneluser_pass);
    $cpanel_ftp = HW_CPanel_Ftp::loadacct($data->acc_id);
    $res = $cpanel_ftp->list_ftp_sessions();
    $result = json_decode($res);
    #ajax_output($res);
    if(!empty($result->errors) /*$result->errors!=null*/) {
        exit();
    }
    echo '<table border="1px" cellspacing="1px" cellpadding="3px" class="hover-table">';
    echo '<tr>
        <td><strong>PIP</strong></td>
        <td><strong>status</strong></td>
        <td><strong>host</strong></td>
        <td><strong>login</strong></td>
        <td>X</td>

    </tr>';
    if(!empty($result->data) && is_array($result->data))
        foreach ($result->data as $item) {
            if(!isset($item->host)) $item->host = '';
            echo '<tr>';
            echo "<td>{$item->pid}</td>";
            echo "<td>{$item->status}</td>";
            echo "<td>{$item->host}</td>";
            echo "<td>{$item->login}</td>";
            echo "<td><a href='javascript:void(0)' onclick='hw_kill_ftpsession(this,{$data->acc_id},{$item->pid})'>Kill</a></td>";
            echo '</td>';
        }
    echo '</table>';
}
add_action('ajax_task_listftp_sessions', 'listftp_sessions');
/**
 * kill a ftp session
 * @param $data
 */
function kill_ftp_session($data) {
    $cmd=_post('cmd');
    $pid = _post('pid');    //process id to kill
    $save = WHM_CPANEL_SHELL_APP ."/x_ssh/commands";

    //modify cmd file, do not put -D param before dbname
    //https://my.bluehost.com/cgi/help/112
    $txt ="kill {$pid}";

    file_put_contents($save.'/'.$cmd, $txt);

    //run batch file
    $data->run_command($cmd);   //in fact you do not need to pass $cmd, it provided by default
}
add_action('ajax_task_kill_ftp_session', 'kill_ftp_session');
/**
 * set ftp password
 * @param $data
 */
function setftp_pass($data) {
    $ftpuser = _post('user');
    $ftppass = _post('pass');

    /*$query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Ftp&cpanel_jsonapi_func=passwd&user={$ftpuser}&pass={$ftppass}";
    $cpanel = HW_CPanel::loadacct_instance($acc_id, false);
    #$cpanel = new HW_CPanel($host, $cpaneluser, $cpaneluser_pass, false);
    $result = $cpanel->cpanelapi($query);*/

    //authorize
    #$cpanel = HW_CPanel::loadacct_instance($acc_id,0);
    #$cpanel_ftp = HW_CPanel_Ftp::init($acc_id);
    $cpanel_ftp = HW_CPanel_Ftp::loadacct($data->acc_id);

    $result = $cpanel_ftp->setftp_pass(array('ftp_user' => $ftpuser, 'ftp_pass' => $ftppass));
    ajax_output($result);

    //save to db
    $result=json_decode($result);
    if(isset($result->cpanelresult->data[0]) && isset($result->cpanelresult->data[0]->result)
        && $result->cpanelresult->data[0]->result == '1')
    {
        $result = add_acctftp(array('ftp_user' => $ftpuser, 'ftp_pass' => $ftppass), $data->acc_id);
        ajax_output($result);
    }
}
add_action('ajax_task_setftp_pass', 'setftp_pass');
/**
 * del ftp account
 * @param $data
 */
function delftp_acct($data) {
    $ftpuser = _post('user');

    /*$query = "json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Ftp&cpanel_jsonapi_func=delftp&user={$ftpuser}&destroy=1";
    $cpanel = HW_CPanel::loadacct_instance($acc_id, false);

    $result = $cpanel->cpanelapi($query);*/
    //authorize
    $cpanel_ftp = HW_CPanel_Ftp::loadacct($data->acc_id);
    $result = $cpanel_ftp->delftp($ftpuser);
    ajax_output($result);
}
add_action('ajax_task_delftp_acct', 'delftp_acct');
