<?php
include_once ('includes/common/require.php');
_load_class('cpanel');
//include_once ('includes/classes/cpanel.class.php');

//check user login
check_user_session();

global $DB;

//form action
if(isset($_POST['submit']) && _post('task') == 'save_acc') {
    $host = isset($_POST['cpanel_host'])? $_POST['cpanel_host'] : '';
    $domain = isset($_POST['cpanel_domain'])? $_POST['cpanel_domain'] : '';
    $user = isset($_POST['cpanel_user'])? $_POST['cpanel_user'] :'';
    $pass = isset($_POST['cpanel_pass'])? ($_POST['cpanel_pass']) : '';
    $email = isset($_POST['cpanel_email'])? ($_POST['cpanel_email']) : '';

    //update acc
    $data = array(
        'cpanel_host' => $host,
        'cpanel_domain' => $domain,
        'cpanel_user' => $user,
        'cpanel_pass' => encrypt($pass),
        'cpanel_email' => $email,

    );
    $res = update_cpacct($data, isset($_POST['acc_id'])? $_POST['acc_id']:'');

    #var_dump($DB->Affected_Rows());

}


//edit acc
if(isset($_GET['id']) && is_numeric($_GET['id']) && _get('do')=='edit') {
    $current = get_cpanel_acc(_get('id'));
    if(!count($current)) unset($current);
}
//delete acc
if(isset($_GET['id']) && _get('do')=='delete') {
    del_cpanel_acc(_get('id'));
}

//list rows
$accts = list_whm_accts();
//get root account
$root_acc = get_cpanel_info(array('cpanel_user' => 'root'));

//update sqlite db used for cron job of backup
if(_get('do') =='update_portable_db') {
    $db = new SQLite3('db/db');
    $hash = encrypt(serialize($accts));
    $r = $db->query("Update hashtext set hash='{$hash}'");
    add_message($r);
}

/**
 * add account
 *
 */
if(_post('task') == 'add_acc') {
    $cpanel = authorize_hwm();
    $acct = array(
        'username' => _post('cp_username'),
        'password' => _post('cp_password'),
        'domain' => _post('cp_domain'),
        'contactemail' => _post('cp_contactemail')
    );
    //create cpanel from whm
    $result = $cpanel->createacct($acct);

    if(isset($result->result) && isset($result->result[0])
        && isset($result->result[0]->status) && $result->result[0]->status) {
        //check for exists acct
        $id = get_cpanel_info(array(
            'cpanel_user' => $acct['username'],
            'cpanel_host' => HW_WHM_IP
        ));
        $_acc_id = isset($id['id'])? $id['id'] : '';

        //add new acct to db
        update_cpacct(array(
            'cpanel_user' => $acct['username'],
            'cpanel_pass' => encrypt($acct['password']),
            'cpanel_domain' => $acct['domain'],
            'cpanel_host' => HW_WHM_IP,
            'cpanel_email' => $acct['contactemail']
        ), $_acc_id);
    }
    if(isset($result->result)) add_message($result->result);else add_message('Failt !');
}
?>
<html>
<head>
    <title>Accounts</title>
    <?php include ('template/head.php');?>
    <script>
        jQuery(document).ready(function() {
            $('.list-accts .table-accts').hw_search_table({inputText: '#search_acct', column:1});
        });

    </script>
</head>
<body>
<div class="wrapper">
    <?php
    //include header
    include ('template/header.php');

    show_messages();
    ?>

    <div class="main">
    <table border="1px" cellpadding="5" cellspacing="0" width="">
        <tr>
            <td valign="top">
                <h2>Manage Accounts</h2>
                <!-- toolbar -->
                <div class="toobar">
                    <a href="account.php" class="button">Add new</a> |
                    <a href="javascript:void(0)" onclick="if(confirm('Do you want to export to portable DB ?')) window.location.href='?do=update_portable_db'" class="button">Update portable db</a>|
                    <a href="javascript:void(0)" onclick="hw_openssh_root(this)" class="button">Open Root Terminal</a> |
                    <a href="javascript:void(0)" onclick="alert('developping');return;hw_complete_generate_sshkey(this,25)" class="button">Generate SSH key for root</a> |
                    <a href="javascript:void(0)" onclick="hw_reset_spoolexim(this)" class="button">Clear Spool exim</a> |
                    <div>
                        <fieldset><legend>Root Account</legend>
                        <?php if(count($root_acc)) {
                            echo '<ul>';
                            echo "<li>User: {$root_acc['cpanel_user']}</li>";
                            printf( "<li>User: %s</li>", decrypt($root_acc['cpanel_pass']));

                            echo '</ul>';
                        }?>
                            </fieldset>
                    </div>
                </div>

                <div class="list-accts">
                    <div>
                        <label for="search_acct">Search by domain:</label> <input type="text" id="search_acct"/>
                    </div><br/>
                    <table border="1px" class="table-accts" cellpadding="3px" cellspacing="1px">
                        <tr>
                            <td><strong>ID</strong></td>
                            <td><strong>Domain</strong></td>
                            <td><strong>user</strong></td>
                            <td><strong>Email</strong></td>
                            <td><strong>Action</strong></td>
                        </tr>
                <?php
                foreach ($accts as $row ) {
                    $edit_link = sprintf(' (<a class="tooltip-top" title="Edit account from manager" href="?id=%s&do=edit">Edit</a>)', $row['id']);
                    $del_link = sprintf(' (<a href="javascript:void(0)" class="tooltip-top" title="Delete account from manager" onclick="hw_del_account_fromdb(\'%s\',\'%s\')">Del</a>)', $row['cpanel_user'], $row['id'] );

                    $test_alive = sprintf(' (<a class="tooltip-top" title="Test account for alive" href="javascript:void(0)" onclick="hw_testacct_connection(this,\'%s\');">Test</a>)', $row['id']);;

                    $create_token_link = sprintf(' (<a class="tooltip-top" title="Create token for cpanel for json api calling" href="javascript:void(0)" onclick="hw_create_accttoken(this,\'%s\')">Create token</a>)', $row['id']);

                    $login_link = sprintf(' (<a href="%s" class="tooltip-top" title="Go cPanel admin page" target="_blank">cPanel</a>)', "https://{$row['cpanel_host']}:2083/");//. trim($row['token'],'/'). '/frontend/paper_lantern/index.html');

                    $enable_ssh = sprintf(' (<a class="tooltip-top" title="Enable SSH Access for the account." href="javascript:void(0)" onclick="hw_enable_sshacct(this,\'%s\')">Enable SSH</a>)', $row['id']);
                    //$generate_sshkey = sprintf(' (<a class="tooltip-top" title="Generate SSH key for SSH Access" href="javascript:void(0)" onclick="hw_generate_sshkey(this, \'%s\')">Generate SSH key</a>)', $row['id']);
                    $gen_sshkey = sprintf(' (<a class="tooltip-top" title="Generate SSH key for SSH Access" href="javascript:void(0)" onclick="hw_complete_generate_sshkey(this, \'%s\')">Generate SSH key</a>)', $row['id']);
                    $test_sshaccess = sprintf(' (<a class="tooltip-top" title="Test SSH Access connection for this account" href="javascript:void(0)" onclick="hw_test_ssh_connection(this, \'%s\')">Test SSH</a>)', $row['id']);;

                    $class = (isset($_GET['id']) && $_GET['id'] == $row['id'])? 'current':'';
                    $row_class = ($row['cpanel_user'] =='root')? 'root_acc' : '';

                    echo '<tr class="'.$row_class.'">';
                    echo "<td>{$row['id']}</td>";
                    echo "<td><a href='http://{$row['cpanel_domain']}' target='_blank'>{$row['cpanel_domain']}</a></td>";

                    echo '<td><div class="item '.$class.'">';
                    echo ($row['cpanel_user'].'@'.$row['cpanel_host']);
                    echo '</div></td>';
                    echo "<td>{$row['cpanel_email']}</td>";
                    echo '<td>'.$edit_link.'|'.$del_link.'|'.$test_alive. '|'. $create_token_link.'|'.$login_link.'<hr/>'.$enable_ssh.'|'.$gen_sshkey.'|'.$test_sshaccess. '</td>';
                    echo '</tr>';
                }
                ?>
                        </table>
                    </div>

            </td>
            <td valign="top">
                <h2>Add account</h2>
                <div style="display: inline-block;width:100%">
                <div style="float:left;">
                <!-- form -->
                <form method="POST">
                    <input type="hidden" name="task" value="save_acc"/>
                    <?php
                    if(isset($current)) echo '<input type="hidden" name="acc_id" value="'.$current['id'].'"/>';
                    ?>
                    <p>
                        <label for="cpanel_host">Cpanel IP</label><br/>
                        <input  name="cpanel_host" value="<?php echo isset($current['cpanel_host'])? $current['cpanel_host']:''?>"/>
                    </p>
                    <p>
                        <label for="cpanel_domain">Cpanel domain</label><br/>
                        <input  name="cpanel_domain" value="<?php echo isset($current['cpanel_domain'])? $current['cpanel_domain']:''?>"/>
                    </p>
                    <p>
                        <label for="cpanel_user">cPanel user</label><br/>
                        <input  name="cpanel_user" value="<?php echo isset($current['cpanel_user'])? $current['cpanel_user']:''?>"/>
                    </p>
                    <p>
                        <label for="cpanel_pass">cPanel pass</label><br/>
                        <input  name="cpanel_pass" id="cpanel_pass" value="<?php echo isset($current['cpanel_pass'])? decrypt($current['cpanel_pass']):''?>"/>
                        (<a href="javascript:void(0)" onclick="hw_generate_strong_pass(this,'#cpanel_pass')">Generate</a>)<br/>
                        <span>Chú ý: do password điền tự động trong các công cụ do vậy không để ký tự symbols sẽ lỗi encode.</span>
                    </p>
                    <p>
                        <label for="cpanel_email">cPanel email</label><br/>
                        <input  name="cpanel_email" value="<?php echo isset($current['cpanel_email'])? ($current['cpanel_email']):''?>"/>
                    </p>

                    <p>
                        <input type="submit" name="submit" value="<?php echo isset($current)? 'Update':'Add'?>"/>
                    </p>
                </form>
                    </div>
                    <div style="float:right;">
                        <?php if(isset($current)) {?>
                            <ul>
                                <?php
                                foreach($current as $key=>$val) {
                                    if(is_numeric($key)) continue;
                                    if($key=='cpanel_pass') $val = decrypt($val);
                                    echo '<li><strong>'.$key.'</strong>: '.$val.'</li>';
                                }
                                ?>
                            </ul>
                        <?php }?>
                    </div>
                </div><hr/>
                <div style="display: inline-block;width:100%">
                    <h2>Subdomain</h2>
                    <form method="POST" id="domain_frm">
                        <p>
                            <label for="acc">Accounts</label><br/>
                            <select name="acc" class="combobox_acc">
                                <option value="">-----select-----</option>
                                <?php foreach($accts as $row){
                                    printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                                }?>
                            </select>
                        <div>
                            <a id="c_subdomain" href="javascript:void(0)" data-form="#domain_frm" onclick="hw_create_subdomain(this,$('#domain_frm select[name=acc]').val())">Create subdomain</a><br/>
                            <a id="d_subdomain" href="javascript:void(0)" data-form="#domain_frm" onclick="hw_del_subdomain(this,$('#domain_frm select[name=acc]').val() )">Del subdomain</a><br/>
                            <a id="list_subdomains" href="javascript:void(0)" data-form="#domain_frm" onclick="hw_list_subdomains(this,$('#domain_frm select[name=acc]').val(),'#subdomains')">List subdomains</a><br/>
                        </div>
                        </p>
                        <p>
                            <label for="subdomain">Subdomain</label>(*)<br/>
                            <input  name="subdomain" value=""/>
                        </p>
                        <p>
                            <label for="subdomain_path">subdomain's document root</label><br/>
                            <input  name="subdomain_path" value="/public_html/"/>
                        </p>

                    </form>
                    <div id="subdomains"></div>
                </div>

            </td>
        </tr>
    </table>
<!-- ------------------------------------------------------------ -->
    <table border="1px" cellpadding="5" cellspacing="0" width="">
        <tr>
            <td valign="top">
                <h2>cPanel Accounts</h2>
                <p>Note: Need root access</p>
                <div>
                    <a href="javascript:void(0)" onclick="hw_cp_list_all(this, '#all_cp_accts')">List all accts</a><br/>
                    <!-- <a href="javascript:void(0)" onclick="hw_cp_list_all(this, '#all_cp_accts')">Update list accts</a> -->
                </div>
                <form method="post" action="account.php?add=1">
                    <input type="hidden" name="task" value="add_acc"/>
                    <p>
                        <div>ID: <strong><?php echo HW_WHM_IP?></strong></div>
                    </p>
                    <p>
                        <label for="cp_domain">cPanel domain</label><br/>
                        <input  name="cp_domain" value=""/><br/>
                        (<em>Không chứa www & http</em>)
                    </p>
                    <p>
                        <label for="cp_username">cPanel username</label><br/>
                        <input  name="cp_username" value=""/>
                    </p>
                    <p>
                        <label for="cp_password">cPanel password</label><br/>
                        <input  name="cp_password" id="cp_password" value=""/>
                         (<a href="javascript:void(0)" onclick="hw_generate_strong_pass(this,'#cp_password')">Generate</a>)
                    </p>

                    <!--
                    <p>
                        <label for="cp_quota">cPanel quota</label><br/>
                        <input  name="cp_quota" value="750"/>
                    </p>
                    <p>
                        <label for="cp_bwlimit">cPanel bwlimit</label><br/>
                        <input  name="cp_bwlimit" value="15000"/>
                    </p>
                    <p>
                        <label for="cp_maxftp">cPanel maxftp</label><br/>
                        <select  name="cp_maxftp" >
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="unlimited">unlimited</option>
                        </select>
                    </p>
                    -->
                    <p>
                        <label for="cp_contactemail">cPanel contactemail</label><br/>
                        <input  name="cp_contactemail" value=""/>
                    </p>
                    <p>
                        <input type="submit" name="submit" value="Create"/>
                    </p>
                </form>

            </td>
            <td valign="top">
                <div id="all_cp_accts"></div>
            </td>
        </tr>
    </table>
        </div>
</div>
</body>
</html>