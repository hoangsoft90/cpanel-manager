<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 18:56
 */
/**
 * set cron job backup
 * @param $data
 */
function add_cronbackup($data) {
    $cpanel_file = $data->get_instance('fileman1');//new HW_CPanel_Fileman($data->host, $data->cpaneluser, $data->cpaneluser_pass);
    //check cron backup for exists
    #$cpanel_file->delcron();
    $args = array(
        "command"=>_post('command'),
        "day"=>_post('day'),
        "hour"=> _post('hour'),
        "minute"=> _post('minute'),
        "month"=> _post('month'),
        "weekday"=> _post('weekday')
    );
    $res = $cpanel_file->create_cron($args);
    $result = json_decode($res);

    if(isset($result->cpanelresult->data) && isset($result->cpanelresult->data[0]->linekey)) {  //cron success creation
        //update cron key for this acc in db
        update_cpacct(array('cron_key' => $result->cpanelresult->data[0]->linekey), $data->acc_id);
    }
    ajax_output($res);
}
add_action('ajax_task_add_cronbackup', 'add_cronbackup');
/**
 * list crons for my backup
 * @param $data
 */
function listcronsbackup($data) {
    $cpanel_file = $data->get_instance('fileman1'); //new HW_CPanel_Fileman($data->host, $data->cpaneluser, $data->cpaneluser_pass);
    $res = $cpanel_file->listcrons();
    $result = json_decode($res);

    if(isset($result->cpanelresult->data)) {
        echo '<table border="1px" cellpadding="3px" cellspacing="1px" class="hover-table">';
        echo '<tr>
            <td><strong>count/id</strong></td>
            <td><strong>day</strong></td>
            <td><strong>hour</strong></td>
            <td><strong>minute</strong></td>
            <td><strong>weekday</strong></td>
            <td><strong>month</strong></td>
            <td><strong>command</strong></td>
            <td><strong>linekey</strong></td>
            <td></td>
        </tr>';
        foreach($result->cpanelresult->data as $row) {
            $row = (array)$row;
            if(!isset($row['day'])) continue;
            echo '<tr>';
            echo "<td>{$row['count']}</td>";
            echo "<td>{$row['day']}</td>";
            echo "<td>{$row['hour']}</td>";
            echo "<td>{$row['minute']}</td>";
            echo "<td>{$row['weekday']}</td>";
            echo "<td>{$row['month']}</td>";
            echo "<td>{$row['command']}</td>";
            echo "<td>{$row['linekey']}</td>";
            echo "<td><a href='javascript:void(0)' data-cpid='{$data->acc_id}' onclick='hw_delcron(this,\"".$row['count']."\")'>Delete</a></td>";
            echo '</tr>';
        }
        echo '</table>';
    }

    #ajax_output($res);
}
add_action('ajax_task_listcronsbackup', 'listcronsbackup');
/**
 * del cron
 * @param $data
 */
function delcron($data) {
    $cpanel_file = $data->get_instance('fileman1');//new HW_CPanel_Fileman($data->host, $data->cpaneluser, $data->cpaneluser_pass);
    $cpanel_file->delcron(_req('id'));
}
add_action('ajax_task_delcron', 'delcron');
/**
 * create backup
 * @param $data
 */
function createbackup($data) {
    $cpanel_file = $data->get_instance('fileman1');//new HW_CPanel_Fileman($data->host, $data->cpaneluser, $data->cpaneluser_pass);
    #$cpanel_file->cre
    $res = $cpanel_file->create_backup();
    ajax_output($res);
    //send mail
    $body = HW_Twig_engine::twig_render('email/createbackup.tpl', array(
        'domain'=>$data->domain,'username'=>$data->cpaneluser,'email_domain'=>$data->email_domain
    ));
    send_mail1($data->email_domain, 'Hoangweb - backup website '.$data->domain, $body);
}
add_action('ajax_task_createbackup', 'createbackup');

/**
 * restore backup
 * @param $data
 */
function restore_backup($data) {
    $cpanel_file = HW_CPanel_Fileman::loadacct($data->acc_id);
    $result = $cpanel_file->restore_backup();
    ajax_output($result);
}
add_action('ajax_task_restore_backup', 'restore_backup');
/**
 * create cpbackup need root access (refer WHM user)
 * @param $data
 */
function create_cpbackup($data) {
    $clone2acc = _post('clone2acc');
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__cpbackup.bat '.$data->acc_id.' '.$clone2acc.'"');
    ajax_output($str);
}
add_action('ajax_task_create_cpbackup', 'create_cpbackup');
/**
 * restore cpbackup file
 * @param $data
 */
function restore_cpbackup($data) {
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__cpbackup-restore.bat '.$data->acc_id.'"');
    ajax_output($str);
}
add_action('ajax_task_restore_cpbackup', 'restore_cpbackup');