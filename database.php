<?php
include_once ('includes/common/require.php');
_load_class('mysql','cpanel');
_load_class('user','cpanel');

//check user login
#check_user_session();

global $DB;

if(isset($_POST['submit'])){
    //validation
    if(_post('acc') == '') {
        add_message("Please select which account you want to use ?");
        goto invalid;
    }

    //get cpanel credentials
    $acc_id = _post('acc');
    $acc = get_cpanel_acc($acc_id);
    $host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
    $cpaneluser = isset($acc['cpanel_user'])? $acc['cpanel_user']:"";
    $cpaneluser_pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :'';
    $email_domain= isset($acc['cpanel_email'])? $acc['cpanel_email']:'laptrinhweb123@gmail.com';

    //db info
    $databaseuser= isset($_POST['dbuser'])? $_POST['dbuser']: "";
    $databasepass=isset($_POST['dbpass'])? $_POST['dbpass']:'';
    $databasename= isset($_POST['dbname'])? $_POST['dbname'] : '';

    $task = _post('task');

    //authorize
    $cpanel = new HW_CPanel($host, $cpaneluser, $cpaneluser_pass);

    if($task == 'adddb'){
        //create database
        if(!empty($databasename)) {
            $cpanel_mysql = HW_CPanel_Mysql::init($cpanel);
            $result = $cpanel_mysql->create_database($databasename);
            add_message($result);

            //save to db
            #add_acct_db(array('cpid' => $acc_id, 'db' => $databasename));  //this should for when add db & user
        }
        else add_message('no specify database name');
    }

    if($task == 'adduser'){
        //create user
        if($databaseuser && $databasepass) {
            $cpanel_user= HW_CPanel_User::init($cpanel);
            $usr = $cpanel_user->create_user($databaseuser, $databasepass);
            add_message($usr);

            //save to db
            add_acct_db(array(
                'cpid' => $acc_id,
                'db' => $databasename,
                'dbuser' => $databaseuser,
                'dbpass' => encrypt($databasepass)
            ));
        }
        else add_message("Can not create user , no provide db user & pass");


    }
    if($task == 'adduser' || $task == 'binduserdb') {
        //add user to db
        if($databaseuser && $databasename) {

            $cpanel_mysql= HW_CPanel_Mysql::init($cpanel);
            $addusr = $cpanel_mysql->add_user2db($databaseuser, str_replace($cpaneluser.'_','',$databasename));
            add_message($addusr);

            //save to db
            $databasepass = '';
            $res_db = get_acct_dbs($acc_id, " and dbuser='$databaseuser' limit 1");
            if($res_db) {
                $row = $res_db->FetchRow();
                if(count($row)) $databasepass = $row['dbpass'];
            }

            add_acct_db(array(
                'cpid' => $acc_id,
                'db' => $databasename,
                'dbuser' => $databaseuser,
                'dbpass' => $databasepass
            ));
        }
        else add_message("can not add user to db, no provide user & db name");
    }

}
invalid:
//delete db from manager
if(_get('do') == 'del_db_from_manager' && is_numeric(_get('id') )) {
    del_acct_db(array('id' => _get('id')));
    add_message('delete db='._get('id') );
}

//list accts
$accts = list_whm_accts();
//$list_dbs = get_acct_dbs();
?>
<html>
<head>
    <title>Database Cpanel</title>
    <?php include ('template/head.php');?>
</head>
<body>
    <div class="wrapper">
        <?php
        //include header
        include ('template/header.php');

        show_messages();
        ?>

        <div class="main">
        <table width="" border="1px" cellpadding="5" cellspacing="0">
            <tr>
                <td>
                    <h2>Database</h2>
                    <form method="POST" ID="myform">
                        <input name="task" type="hidden" value="adddb"/>
                        <p>
                            <label for="acc">Accounts</label><br/>
                            <select name="acc" class="combobox_acc">
                                <option value="">-----select-----</option>
                                <?php foreach($accts as $row){
                                    printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                                }?>
                            </select>
                            <div>
                                <a id="listdbs" href="javascript:void(0)" onclick="hw_list_dbs(this,$('#myform select[name=acc]').val(),'#listdbs_container')">List databases</a><br/>
                                <a id="listdbtable" href="javascript:void(0)" onclick="hw_list_dbtable_data(this,$('#myform select[name=acc]').val(),'#listdbs_container')">List mysql table data</a><br/>

                                <a id="listusers" href="javascript:void(0)" onclick="hw_list_users(this,$('#myform select[name=acc]').val(),'#listusers_container')">List Users</a><br/>
                                <a id="list_users_db" href="javascript:void(0)" onclick=""><i>List users db</i></a><br/>

                            <a id="" title="Export current wp database in sql file" href="javascript:void(0)" data-form="#myform" onclick="hw_exportdb(this,$('#myform select[name=acc]').val())">Export databases</a><br/>
                            <a id="" title="" href="javascript:void(0)" data-form="#myform" onclick="hw_importdb(this,$('#myform select[name=acc]').val())">Import databases</a><br/>
                            </div>
                        </p>
                        <p>
                            <label for="dbname">DB Name</label><br/>
                            <input  name="dbname" value=""/>
                            (<em>Chú ý: không viết tên account. Ie sai: hwvn_db1</em>)
                        </p>
                        <p>
                            <label for="file">File</label><br/>
                            <select name="file">
                                <?php
                                foreach(list_files_in_folder(ROOT_DIR. '/tmp/files') as $file) {
                                    if($file =='.' || $file=='..') continue;
                                    printf('<option value="%s">%s</option>', ROOT_DIR. '/tmp/files'.'/'.$file, $file);
                                }
                                ?>
                            </select>
                            <em>(Trong thư mục tmp/files)</em>
                        </p>
                        <p>
                            <input type="submit" name="submit" value="Add"/>
                        </p>
                    </form>

                </td>
                <td>
                    <fieldset>
                         <legend>Databases</legend>
                        <div id="listdbs_container"></div>
                    </fieldset>
                    <fieldset>
                        <legend>Users</legend>
                        <div id="listusers_container"></div>
                    </fieldset>
                </td>
                <td valign="top">
                    <div id="listdbs_container1"></div>
                    <?php /*
                    <table border="1px" cellpadding="3px" cellspacing="1px">
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
                        </tr>
                        <?php while($row = $list_dbs->FetchRow()) {?>
                            <tr>
                                <td><strong><?php echo $row['id']?></strong></td>
                                <td><?php echo $row['cpid']?></td>
                                <td><?php echo $row['cpanel_domain']?></td>
                                <td><?php echo $row['cpanel_user']?></td>
                                <td><?php echo $row['cpanel_email']?></td>

                                <td><?php echo $row['db']?></td>
                                <td><?php echo $row['dbuser']?></td>
                                <td><?php echo $row['dbpass']?></td>
                                <td><a href="javascript:void(0)" onclick="if(confirm('Are you sure to delete this db [<?php echo $row['db']?>]?')) window.location.href='?do=del_db_from_manager&id=<?php echo $row['id']?>'">Del from db</a></td>
                            </tr>
                        <?php }?>
                    </table>
                    <div>Chú ý: Nhập chỉ số ID trong tool cài đặt WP tự động để cấu hình wp-config.php</div>
 */ ?>
                </td>
            </tr>
        </table>

        <div>
            <table border="1px" cellpadding="5" cellspacing="0" width="">
                <tr>
                    <td>
                        <h2>DB User & Binding</h2>
                        <form method="POST" ID="myform_user" onsubmit="return myform_submit(this)">
                            <input type="hidden" name="task" value="adduser"/>
                            <p>
                                <label for="acc">Accounts</label><br/>
                                <select name="acc" class="combobox_acc">
                                    <option value="">-----select-----</option>
                                    <?php foreach($accts as $row){
                                        printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                                    }?>
                                </select>
                            <div>
                                <a class="listdbs" href="javascript:void(0)" data-form="#myform_user" onclick="hw_list_dbs(this,$('#myform_user select[name=acc]').val(), listdbs_callback,1)">List databases</a>(So sánh lưu trong DB)<br/>
                            </div>
                            </p>
                            <p>
                                <label for="dbname">DB Name</label><br/>
                                <select name="dbname" ></select>
                            </p>
                            <p>
                                <label for="dbuser">DB User</label><br/>
                                <input  name="dbuser" value=""/>(<em>Chú ý: tên DB User không chứa tên tiền tố account. ie sai: hwvn_user1</em>)
                            </p>
                            <p>
                                <label for="dbpass">DB Pass</label><br/>
                                <input  name="dbpass" id="dbpass" value=""/>
                                (<a href="javascript:void(0)" onclick="hw_generate_strong_pass(this,'#dbpass')">Generator</a>)
                            </p>
                            <p>
                                <input type="submit" name="submit" value="Add"/>
                            </p>
                        </form>

                    </td>
                    <td>
                        <h2>User DB Binding</h2>
                        <form method="POST" ID="myform_binding">
                            <input type="hidden" name="task" value="binduserdb"/>
                            <p>
                                <label for="acc">Accounts</label><br/>
                                <select name="acc" class="combobox_acc">
                                    <option value="">-----select-----</option>
                                    <?php foreach($accts as $row){
                                        printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                                    }?>
                                </select>
                            <div>
                                <a class="listdbs" href="javascript:void(0)" data-form="#myform_binding" onclick="hw_list_dbs(this,$('#myform_binding select[name=acc]').val(), listdbs_callback,1)">List databases</a>(So sánh lưu trong DB)<br/>
                                <a id="listusers" href="javascript:void(0)" data-form="#myform_binding" onclick="hw_list_users(this,$('#myform_binding select[name=acc]').val(),listusers_callback,1)">List Users</a><br/>
                            </div>
                            </p>
                            <p>
                                <label for="dbname">DB Name</label><br/>
                                <select name="dbname" ></select>
                            </p>
                            <p>
                                <label for="dbuser">DB User</label><br/>
                                <select  name="dbuser" ></select>
                            </p>
                            <p>
                                <input type="submit" name="submit" value="Add"/>
                            </p>
                        </form>

                    </td>
                </tr>
            </table>


            <script>
                /**
                 * list all dbs in acct
                 * @param frm
                 * @param data
                 */
                function listdbs_callback(frm,data) {
                    hw_listsaved_dbs(null,$(frm+' select[name=acc]').val(), '#listdbs_container1');
                    try{
                        data = JSON.parse(data);
                        $(frm+' [name=dbname]').empty();
                        for(var key in data) {
                            $(frm+' [name=dbname]').append('<option value="'+key+'">'+data[key]+'</option>');
                        };
                    }
                    catch(e){
                        console.log(e);
                    }

                }
                /**
                 * list users in current acct callback
                 * @param frm
                 * @param data
                 */
                function listusers_callback(frm,data) {console.log(data);
                    try{
                        data = JSON.parse(data);
                        $(frm+' [name=dbuser]').empty();
                        for(var key in data) {
                            $(frm+' [name=dbuser]').append('<option value="'+key+'">'+data[key]+'</option>');
                        };
                    }
                    catch(e){
                        console.log(e);
                    }
                }
            </script>
        </div>
            </div>
    </div>
</body>
</html>