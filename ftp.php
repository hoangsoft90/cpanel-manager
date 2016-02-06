<?php
//include libs
include_once ('includes/common/require.php');
_load_class('cpanel');
_load_class('ftp', 'cpanel');


//check user login
check_user_session();

global $DB;

if(isset($_POST['submit'])) {
    //validation
    if(_post('acc') == '') {
        add_message("Please select which account you want to use ?");
        goto invalid_form;
    }
    //get cpanel credentials
    /*$acc = get_cpanel_acc(_post('acc'));
    $host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
    $cpaneluser = isset($acc['cpanel_user'])? $acc['cpanel_user']:"";
    $cpaneluser_pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :'';
    $email_domain= isset($acc['cpanel_email'])? $acc['cpanel_email']:'laptrinhweb123@gmail.com';
    */
    //authorize
    #$cpanel = new HW_CPanel($host, $cpaneluser, $cpaneluser_pass,false);
    $cpanel = HW_CPanel::loadacct_instance(_post('acc'),0);

    //add an ftp
    if(_post('task') == 'add_ftp') {
        //authorize
        #$cpanel_ftp = new HW_CPanel_Ftp($host, $cpaneluser, $cpaneluser_pass);
        $cpanel_ftp = HW_CPanel_Ftp::init($cpanel);
        $ftp = array(
            'user' => _post('ftp_username'),
            'pass' => _post('ftp_passwd'),
            'quota' => _post('ftp_quota'),
            'homedir' => _post('ftp_homedir'),
            //'cpid' => _post('acc')
        );
        $res = $cpanel_ftp->create_ftp($ftp);
        add_message($res);

        if(is_string($res)) $res =json_decode($res);
        if(isset($res->cpanelresult->event->result) && $res->cpanelresult->event->result=='1') {    //success ftp creation
            //save ftp acct to db
            if(_post('acc')) {
                $rs= add_acctftp(array('ftp_user' => $ftp['user'], 'ftp_pass' => $ftp['pass'], 'path'=>$ftp['homedir']), _post('acc'));
                add_message($rs);
            }
        }
    }
}
invalid_form:
//list saved accounts
$accts = list_whm_accts();
?>
<html>
<head>
    <title>FTP - Manager</title>
    <?php include ('template/head.php');?>

    <script src="assets/js/ftp.js"></script>
</head>
<body>
<div class="wrapper">
    <?php
    //include header
    include ('template/header.php');

    show_messages();

    ?>
    <div class="main">
    <table border="1px" cellpadding="5" cellspacing="0" width="70%">
        <tr>
            <td valign="top">
                <h2>FTP</h2>

                <div class="list-accts">
                    <?php
                    /*foreach ($accts as $row ) {
                        $edit_link = sprintf(' (<a href="?id=%s&do=edit">Edit</a>)', $row['id']);
                        $del_link = sprintf(' (<a href="?id=%s&do=delete">Del</a>)', $row['id']);

                        $class = (isset($_GET['id']) && $_GET['id'] == $row['id'])? 'current':'';
                        echo '<div class="item '.$class.'">'.($row['cpanel_user'].'@'.$row['cpanel_host']).$edit_link.'|'.$del_link.'</div>';
                    }*/
                    ?>
                </div>
                <form method="post" id="myform_ftp">
                    <input type="hidden" name="task" value="add_ftp"/>
                    <p>
                        <label for="acc">Accounts</label><br/>
                        <select name="acc" class="combobox_acc">
                            <option value="">-----select-----</option>
                            <?php foreach($accts as $row){
                                printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                            }?>
                        </select>
                    <div>
                        <div>Note: select account above.</div>
                        <div class="links">
                            <a id="listftp" href="javascript:void(0)" onclick="hw_listftp(this,$('#myform_ftp select[name=acc]').val(),'#listftp_container')">List FTP</a><br/>
                            <a id="list-ftpsessions" href="javascript:void(0)" onclick="hw_listftp_sessions(this, $('#myform_ftp select[name=acc]').val(), '#listftp_container')">List FTP Sessions</a><br/>
                        </div>

                    </div>
                    </p>
                    <p>
                        <label for="ftp_username">FTP user</label><br/>
                        <input  name="ftp_username" value=""/>
                    </p>
                    <p>
                        <label for="ftp_passwd">FTP Pass</label><br/>
                        <input  name="ftp_passwd" id="ftp_passwd" value=""/>
                         (<a href="javascript:void(0)" onclick="hw_generate_strong_pass(this,'#ftp_passwd')">Generator</a>)
                    </p>
                    <p>
                        <label for="ftp_quota">FTP quota</label><br/>
                        <input  name="ftp_quota" value="0"/>
                    </p>
                    <p>
                        <label for="ftp_homedir">FTP homedir</label><br/>
                        <input  name="ftp_homedir" value="/"/>
                    </p>
                    <p>
                        <input type="submit" name="submit" value="Create FTP"/>
                    </p>
                </form>

            </td>
            <td valign="top">
                <div id="listftp_container"></div>
            </td>
        </tr>
    </table>
        </div>
</div>
</body>
</html>