<?php
include_once ('includes/common/require.php');
_load_class('cpanel');

//check user login
check_user_session();

global $DB;

//list accts
$accts = list_whm_accts();

//list dbs
//$list_dbs = get_acct_dbs();

?>
<html>
<head>
    <title>Wordpress</title>
    <?php include ('template/head.php');?>

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
                <td>
                    <h2>WORDPRESS</h2>
                    <form method="POST" ID="myform">
                        <input name="task" type="hidden" value="adddb"/>
                        <p>
                        <div class="ui-widget">
                            <label for="acc">Accounts</label><br/>
                            <select name="acc" class="combobox_acc">
                                <option value="">-----select-----</option>
                                <?php foreach($accts as $row){
                                    printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                                }?>
                            </select>
                            </div>
                        <div>
                            <fieldset>
                                <legend>Tools</legend>
                                <a class="" href="javascript:void(0)" data-form="#myform" onclick="hw_install_wp(this,$('#myform select[name=acc]').val() )">Install WordPress</a><br/>
                                <!-- <a id="" href="javascript:void(0)" onclick="hw_wp_upload_codeweb(this,$('#myform_fileman select[name=acc]').val() )">Upload WP</a><br/> -->
                                <!-- <a id="" href="javascript:void(0)" onclick="hw_wp_upload_hwplugins(this,$('#myform_fileman select[name=acc]').val() )">Upload HW Plugins</a><br/> -->

                                <a class="listdbs" href="javascript:void(0)" data-form="#myform" onclick="hw_list_dbs(this,$('#myform select[name=acc]').val(), listdbs_callback,1)">List databases</a><br/>
                                <a id="listdbtable" href="javascript:void(0)" onclick="hw_list_dbtable_data(this,$('#myform select[name=acc]').val(),'#listdbs_container')">List mysql table data</a><br/>

                                <a id="listusers" href="javascript:void(0)" data-form="#myform" onclick="hw_list_users(this,$('#myform select[name=acc]').val(),listusers_callback,1)">List Users</a><br/>

                                <a id="" title="" href="javascript:void(0)" data-form="#myform" onclick="hw_wp_maintenance(this,$('#myform select[name=acc]').val())">Turn Maintance Mode</a><br/>

                                <a id="" title="" href="javascript:void(0)" data-form="#myform" onclick="hw_wp_plugins_configuration(this,$('#myform select[name=acc]').val())">Install Major Plugins</a><br/>

                            </fieldset>
                            <fieldset><legend>WP Config</legend>
                                <a class="tooltip" title="Lấy thông tin db-user đã lưu" id="" href="javascript:void(0)" data-form="#myform" onclick="hw_update_wpconfig(this,$('#myform select[name=acc]').val())">Update DB-User wp-config.php</a><br/>

                                <a id="" href="javascript:void(0)" data-form="#myform" onclick="hw_list_wpusers(this,$('#myform select[name=acc]').val(),'#result_container1')">List WP Users</a><br/>
                                <a id="" href="javascript:void(0)" data-form="#myform" onclick="hw_list_wpusers_roles(this,$('#myform select[name=acc]').val(),'#result_container1')">List WP Users roles</a><br/>

                                <a id="" class="tooltip" title="Reset mật khẩu cho mọi user. Note: Liệt kê danh sách users để biết." href="javascript:void(0)" data-form="#myform" onclick="hw_reset_wpadmin(this,$('#myform select[name=acc]').val(),'#result_container')">Reset WP User/admin</a><br/>

                                <a id="" class="tooltip" title="Tạo user mới." href="javascript:void(0)" data-form="#myform" onclick="hw_create_wpuser(this,$('#myform select[name=acc]').val(),'#result_container1')">Create WP User</a><br/>

                                <a id="" class="tooltip" title="Bật debug trong wp-config.php" href="javascript:void(0)" data-form="#myform" onclick="hw_enable_wpdebug(this, $('#myform select[name=acc]').val(), '#result_container1')">Enable debug</a><br/>
                            </fieldset>
                            <fieldset><legend>WP Plugins</legend>
                                <a id="" href="javascript:void(0)" data-form="#myform" onclick="hw_wp_deactive_plugins(this,$('#myform select[name=acc]').val(), '#result_container1')">Deactive all plugins</a><br/>

                                <a id="" href="javascript:void(0)" data-form="#myform" onclick="hw_wp_active_plugins(this, $('#myform select[name=acc]').val(), '#result_container1')" class="tooltip" title="Chú ý: khi kích hoạt plugin trong database phải login lại để xác nhận cấu hình">Active plugins</a><br/>

                                (<em>Chú ý: khi kích hoạt plugin trong database phải login lại để xác nhận cấu hình</em>).<br/>

                                <a id="" href="javascript:void(0)" data-form="#myform" onclick="hw_wp_list_plugins(this, $('#myform select[name=acc]').val(), '#result_container1')">List all plugins</a><br/>
                            </fieldset>
                            <fieldset>
                                <legend>MySQL</legend>
                                <p>
                                    <label for="findstr">Find</label><br/>
                                    <input type="text" name="findstr"/>
                                </p>
                                <p>
                                    <label for="replstr">Replace</label><br/>
                                    <input type="text" name="replstr"/>
                                </p>
                                <a id="" href="javascript:void(0)" data-form="#myform" onclick="hw_wp_fixedbaseurl_mysql(this,$('#myform select[name=acc]').val())">Fixed Base URL</a><br/>
                            </fieldset>
                        </div>
                        </p>
                        <p>
                            <label for="subdomain">Subdomain</label><br/>
                            <input type="text" name="subdomain" />
                            <br/><em>(subdomain/folder for WP site)</em>
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
                            <input type="submit" name="submit" disabled="disabled" value="Add"/>
                        </p>
                    </form>
                    <script>
                        /**
                         * list all dbs in acct
                         * @param frm
                         * @param data
                         */
                        function listdbs_callback(frm,data) {
                            hw_listsaved_dbs(null,$(frm+' select[name=acc]').val(), '#listdbs_container');
                            try{
                                data = JSON.parse(data);
                                $(frm+' [name=dbname]').empty();
                                for(var key in data) {
                                    $(frm+' [name=dbname]').append('<option value="'+key+'">'+data[key]+'</option>');
                                }

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
                        function listusers_callback(frm,data) {
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

                </td>
                <td valign="top">
                    <div id="listdbs_container"></div>
                    <div id="result_container1"></div>
                    <div id="result_container2"></div>
                </td>
            </tr>
            <tr>
                <td colspan="2"><div id="result_container"></div></td>
            </tr>
        </table>

    </div>
</div>
</body>
</html>