<?php
include_once ('includes/common/require.php');
_load_class('cpanel');

//check user login
check_user_session();

global $DB;


/**
 * cpanel domain account
 */
$acc_id = '4';  //refer to hoangweb.vn
$acc = get_cpanel_acc($acc_id);
$host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
$domain = isset($acc['cpanel_domain'])? $acc['cpanel_domain']: '';
$cpaneluser = isset($acc['cpanel_user'])? $acc['cpanel_user']: HW_WHM_ROOT_USER;
$cpaneluser_pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :HW_WHM_ROOT_PASS;
$email_domain= isset($acc['cpanel_email'])? $acc['cpanel_email']:'laptrinhweb123@gmail.com';

define('SVN_REPOSITORY_URL', 'http://svn.hoangweb.vn');

$task = _post('task');  //detect job

if(isset($_POST['submit'])) {
    if($task =='addsvn_user') {
        $user = array(
            'svn_user' => _post('svn_user'),
            'svn_pass' => encrypt(_post('svn_user_pass')),
            'svn_fullname' => _post('svn_fullname'),
            'svn_email' => _post('svn_user_email')
        );
        //add new or update user
        $res = hw_svn_adduser($user, _post('user'));
        add_message($res? "Add svn user [{$user['svn_user']}] successful !":"Add svn user failt !");
    }
}

//list accts
$accts = list_whm_accts();

//list dbs
//$list_dbs = get_acct_dbs();
//list all svn users
$users = hw_svn_list_users();

//get user to edit
if(_get('do')=='edituser') {
    $user = hw_svn_get_user(array('svn_user' => _get('user')));
    if(!count($user)) unset($user);
}
//get all repositories saved from local
$repositories = hw_svn_get_repositories();
?>
<html>
<head>
    <title>Subversion</title>
    <?php include ('template/head.php');?>

    <script src="assets/js/svn.js"></script>
    <script src="assets/js/wordpress.js"></script>

</head>
<body>
<div class="wrapper">
    <?php
    //include header
    include ('template/header.php');

    show_messages();
    ?>

    <div class="main">
        <table border="1px" cellpadding="3px" cellspacing="1px">
            <tr>
                <td valign="top">
                    <h2>SVN Subversion</h2>
                    <form method="POST" ID="myform">
                        <input type="hidden" name="acc" value="<?php echo $acc_id?>"/><!-- acc: hoangweb.vn -->
                        <input type="hidden" name="subdomain" value=""/><!-- subdomain: / -->
                        <p>
                            <strong>Subversion URL</strong>: <a href="<?php echo SVN_REPOSITORY_URL?>" target="_blank"><?php echo SVN_REPOSITORY_URL?></a>
                            <br/><br/>
                            <span>Add repository to <?php echo SVN_REPOSITORY_URL?>/themes</span>
                        </p>
                        <p>
                        <fieldset><legend>Tools</legend>
                            <a class="tooltip" title="Re-create svn_custom.conf file on (whm-cpanel-shell) project." href="javascript:void(0)" onclick="build_svn_custom_conf(this, '#myform')">Rebuild svn_custom.conf</a>
                        </fieldset>

                        <fieldset><legend>Reponsitory</legend>
                            <a class="tooltip" title="Create repository on <?php echo SVN_REPOSITORY_URL?>/themes<br/>- Update lastest svn_custom.conf from server to local or keep from local.<br/>- Upload svn_custom.conf to server.<br/>- Update user credential get from localhost.<br/>- Cho phép thay đổi user sử dụng cho repository.<br/>- Thêm vào `svn_repositories` table trên localhost." href="javascript:void(0)" onclick="hw_svn_createrepo(this,'#myform')" >Create repository</a><br/>
                            <a class="tooltip" title="Delete repository from <?php echo SVN_REPOSITORY_URL?>" href="javascript:void(0)" onclick="hw_svn_delrepo(this,'#myform')">Del repository</a><br/>
                            <a class="tooltip" title="Dump a repository from <?php echo SVN_REPOSITORY_URL?>" href="javascript:void(0)" onclick="hw_svn_dumprepo(this,'#myform')">Dump a repository</a><br/>

                            <hr/>
                            <a class="tooltip" title="List all actived repositories<br/>- Delete repository on server." href="javascript:void(0)" onclick="hw_svn_list_repositories(this,'#myform', '#result_container')">List all repositories</a><br/>

                        </fieldset>
                        </p>
                        <p>
                        <fieldset><legend>SVN Users</legend>
                            <a class="tooltip" title="Get all svn users on <?php echo $domain?> from `svn_wp_users`." href="javascript:void(0)" onclick="hw_list_dbtable_data(this, $('#myform [name=acc]:eq(0)').val(),'#result_container', {table:'svn_wp_users'})" data-form="#myform">Check all wp-svn users from <?php echo $domain?>.</a><br/>

                            <a class="tooltip" title="Update all svn users from `svn_wp_users` on localhost. Make sure you sync this table between localhost & server.<br/>Cập nhật thông tin user lên svn server." href="javascript:void(0)" onclick="hw_svn_update_all_wpusers(this,$('#myform [name=acc]:eq(0)').val() )" data-form="#myform">Update all wp-svn users from localhost.</a><br/>

                            <a class="tooltip" title="Fetch wp users on <?php echo $domain?>. Nếu tạo/sửa user trên website trước đó có thể nhấn vào đây để cập nhật về localhost." href="javascript:void(0)" onclick="hw_svn_update_list_wpusers(this,$('#myform [name=acc]:eq(0)').val() )" data-form="#myform">Fetch sync wp-svn users.</a><br/>

                            <hr/>
                            <a class="tooltip" title="Export `svn_wp_users` + `svn_repositories` table from localhost." href="javascript:void(0)" onclick="hw_svn_export_sql_users(this,$('#myform [name=acc]:eq(0)').val() )" data-form="#myform">Export SVN tables .sql</a><br/>

                            <a class="tooltip" title="Import svn-data.sql from tmp/ on <?php echo $domain?>. Make sure you export this file in tmp/ with above link." href="javascript:void(0)" onclick="hw_svn_import_svn_users(this,$('#myform [name=acc]:eq(0)').val() )" data-form="#myform">Import tmp/svn-data.sql on <?php echo $domain?>.</a><br/>

                        </fieldset>
                        </p>
                        <p>
                            <label for="svn_user">User:</label><br/>
                            <select name="svn_user" >
                                <?php foreach($users as $id=>$_user ) {
                                    $name = $_user['svn_user'].($_user['svn_fullname']? '-'.$_user['svn_fullname'] : '');
                                    if($_user['domain']) $name .= " - [{$_user['domain']}]";
                                    //set focus
                                    $selected_item = _get('user') == $_user['svn_user']? 'selected="selected"' : '';

                                    printf('<option %s value="%s">%s</option>', $selected_item, $_user['svn_user'], $name);
                                }?>

                            </select>
                            <div>
                            (<a href="javascript:void(0)" onclick="location.href='?do=edituser&user='+$('#myform [name=svn_user]:eq(0)').val()">Edit</a>|
                            <a href="javascript:void(0)" data-form="#myform" onclick="hw_svn_deluser(this, $('#myform [name=acc]:eq(0)').val())">Delete</a>
                            )
                            <ul>
                                <li>Note: Xóa svn user sẽ xóa trên bảng `svn_wp_users` localhost, server nhưng không xóa svn repository user trên SVN Server. Để xóa svn repository user hãy xóa repository, khi không còn user đó gán cho repository nào thì xóa thông tin user ra khỏi SVN Server.</li>
                            </ul>
                            </div>
                        </p>
                        <p>
                            <label for="new_repo">Repository Name:</label><br/>
                            <input type="text" name="new_repo" value=""/>
                            <a href="javascript:void(0)" onclick="hw_svn_openUrl($('#myform [name=new_repo]:eq(0)').val());" target="_blank">Open repository</a>
                        </p>
                        <p>
                            <input type="submit" name="submit" disabled="disabled" value="Submit"/>
                        </p>

                    </form>
                </td>
                <td valign="top">
                    <!-- create SVN user -->
                    <h2>SVN Users</h2>
                    <form method="POST" action="" name="svn_user_frm" id="svn_user_frm">
                        <input type="hidden" name="acc"  value="<?php echo $acc_id?>"/>
                        <input type="hidden" name="task"  value="addsvn_user"/>
                        <input type="hidden" name="user" value="<?php echo isset($user['id'])? $user['id']: ''?>"/>
                        <p>
                            <label for="svn_fullname">SVN Full Name</label><br/>
                            <input type="text" name="svn_fullname" value="<?php echo isset($user['svn_fullname'])? $user['svn_fullname'] : ''?>"/>
                        </p>
                        <p>
                            <label for="svn_user">SVN User</label><br/>
                            <input type="text" name="svn_user" value="<?php echo isset($user['svn_user'])? $user['svn_user'] : ''?>"/>
                        </p>
                        <p>
                            <label for="svn_user_pass">SVN Pass</label><br/>
                            <input type="text" name="svn_user_pass" id="svn_user_pass" value="<?php echo isset($user['svn_pass'])? decrypt($user['svn_pass']) : ''?>"/>
                            (<a href="javascript:void(0)" onclick="hw_generate_strong_pass(this,'#svn_user_pass')">Generate</a>)
                            <div>
                            <strong>Chú ý:</strong><br/>
                            <em>Nguyên tắc: Mọi thứ xuất phát từ ứng dụng này và đồng bộ lên server.</em><br/>
                            <em>Thuật ngữ: localhost = ứng dụng này.</em>
                                <ul>
                                    <li>Form này để tạo wp user & đồng bộ với dữ liệu tại đây. Nên tạo user từ ứng dụng này ở tab "Subvesion" hoặc "Wordpress", hoặc có thể tạo user trực tiếp trên Wordpress website sau đó đồng bộ về ứng dụng này.</li>
                                    <li>Table `svn_repositories`<->`svn_wp_users` (localhost & hosting) + Wordpress `wp_users` table phải được đồng bộ về users. </li>
                                    <li>Tự động lấy mật khẩu của user lưu trong table `svn_wp_users` được đồng bộ với tài khoản wordpress site và thiết lập trong quá trình tạo repository cho user.</li>
                                    <li>Sửa mật khẩu trong quá trình tạo repository, lấy từ bảng `svn_wp_users` trên website được export từ ứng dụng quản lý cpanel này. <br/>Do vậy lưu ý mọi thay đổi tài khoản svn thực hiện tại đây. Khi tạo user mới/cập nhật user từ localhost hãy:
                                        <ul>
                                            <li>1. Nhấn Export SVN Users .sql</li>
                                            <li>2. Nhấn Import tmp/svn_wp_users.sql on hoangweb.vn.</li>
                                        </ul>
                                    </li>
                                    <li>Export table `svn_wp_users` vào database sử dụng trên (svn) wordpress site.</li>
                                    <li>Nếu muốn cập nhật lại svn user pass, thì chọn bất kỳ repository tồn tại nào và chọn user muốn thay đổi, chọn No trong quá trình khởi tạo repository để không (override).</li>
                                </ul>
                            </div>
                        </p>
                        <p>
                            <label for="svn_user_email">SVN Email</label><br/>
                            <input type="text" name="svn_user_email" value="<?php echo isset($user['svn_email'])? $user['svn_email'] : ''?>"/>
                        </p>
                        <p>
                            <a class="tooltip" title="Create svn user" href="javascript:void(0)" onclick="hw_svn_adduser(this,'#svn_user_frm')">Create/update svn user</a><br/>

                        </p>
                        <p>
                            <input type="submit" name="submit" disabled="disabled" value="<?php echo isset($user)? 'Update': 'Create'?>"/>
                            <input type="reset" value="Reset"/>
                        </p>
                    </form>
                </td>
            </tr>
            <tr >
                <td valign="top">
                    <?php ?>
                    <h2>List repositories from localhost</h2>
                    <p>
                        Chú ý:
                        <ul>
                            <li>Tạo repository chỉ lưu vào `svn_repositories` trên localhost. Bảng này không quan trọng, trường hợp lỗi svn_custom.conf (Hiếm) có thể lấy dữ liệu repository + svn user từ bảng này và cần đồng bộ `svn_repositories` từ localhost lên server.</li>
                            <li>Xóa repository cũng tương tự chỉ xóa `svn_repositories` trên localhost. Nếu cần sử dụng bảng này thì đồng bộ lên server.</li>
                        </ul>
                    </p>
                    <form method="POST" id="repository_frm">
                        <input type="hidden" name="acc" value="<?php echo $acc_id?>"/>
                        <div class="svn_tools">
                            <a href="javascript:void(0)" data-acc="<?php echo $acc_id?>" onclick="hw_svn_fixsvn_hooks(this, '#repository_frm')" class="tooltip-top" title="Fix repository hooks">Update repository hooks</a>|
                            <a href="javascript:void(0)" data-acc="<?php echo $acc_id?>" onclick="hw_svn_del_demoszip(this, '#repository_frm')" class="tooltip-top" title="Delete demos zip folders from repositories.">Del demos zip</a>
                        </div>
                    <table border="1px" cellpadding="4px" cellspacing="1px" class="hover-table">
                        <tr>
                            <td></td>
                            <td><input type="checkbox" onclick="hw_checkall(this)"/> </td>
                            <td><strong>svn_user_id</strong></td>
                            <td><strong>svn_fullname</strong></td>
                            <td><strong>svn_user</strong></td>
                            <td><strong>svn_pass</strong></td>

                            <td><strong>svn_email</strong></td>
                            <td><strong>repository</strong></td>
                            <td><strong>domain</strong></td>
                            <td><strong></strong></td>
                        </tr>
                    <?php
                    $count=0;
                    while($row = $repositories->FetchRow() ) {
                        $count++;
                        $theme_name = $row['repository_name'];
                        if($row['svn_pass']) $row['svn_pass'] = decrypt($row['svn_pass']);  //decypt password
                        //delete repository link
                        $del_repo = "<a class='tooltip-top' title=\"Delete repository on server.\" href='javascript:void(0)' data-acc=\"{$acc_id}\" data-repository=\"{$theme_name}\" onclick='hw_svn_delrepo(this)'>Del repository</a>";
                        //preview
                        $demo_web = "<a class='tooltip-top' title=\"Get demo link\" data-acc=\"{$acc_id}\" data-repository=\"{$theme_name}\" href='javascript:void(0)' target=\"\" onclick=\"hw_svn_get_demo_link(this)\">Preview</a>";

                        //remove demo extract folder on public_html/demo/{$repository_name}
                        $remove_demo_repo = "<a class='tooltip-top' title=\"Delete extracted folder from this repository for web demo\" href='javascript:void(0)' data-acc=\"{$acc_id}\" data-repository=\"{$theme_name}\" onclick='hw_svn_delextract_repo(this, null)'>Del demo</a>";
                        $freeze_repo = "<a class='tooltip-top' title=\"Lock SVN repository\" data-acc=\"{$acc_id}\" data-repository=\"{$theme_name}\" href='javascript:void(0)' target=\"\" onclick=\"hw_svn_freeze_repository(this)\">Freeze</a>";


                        echo '<tr>';
                        echo "<td>{$count}</td>";
                        echo "<td><input type='checkbox' name='repo_list[]' value=\"{$theme_name}\"/></td>";
                        echo "<td>{$row['svn_user_id']}</td>";
                        echo "<td>{$row['svn_fullname']}</td>";
                        echo "<td>{$row['svn_user']}</td>";
                        echo "<td>{$row['svn_pass']}</td>";
                        echo "<td>{$row['svn_email']}</td>";
                        echo "<td><a target='_blank' href=\"".SVN_REPOSITORY_URL."/themes/{$theme_name}\">{$theme_name}</a></td>";
                        echo "<td>{$row['domain']}</td>";

                        echo "<td>{$del_repo} | {$remove_demo_repo} | {$demo_web}</td>";
                        echo "</tr>";
                    }
                    ?>
                        </table>
                     </form>
                </td>
                <td colspan="" valign="top">
                    <div id="result_container"></div>
                </td>
            </tr>
        </table>


    </div>
</div>
</body>
</html>