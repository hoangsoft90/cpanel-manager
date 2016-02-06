<?php
//include libs
include_once ('includes/common/require.php');
_load_class('cpanel');
_load_class('fileman', 'cpanel');


//check user login
check_user_session();

global $DB;
$task = _post('task');

if(isset($_POST['submit'])) {
    //validation
    if(_post('acc') == '') {
        add_message("Please select which account you want to use ?");
        goto invalid_form;
    }

    //get cpanel credentials
    $acc_id = _post('acc');
    $acc = get_cpanel_acc($acc_id);
    $host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
    $cpaneluser = isset($acc['cpanel_user'])? $acc['cpanel_user']:"";
    $cpaneluser_pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :'';
    $email_domain= isset($acc['cpanel_email'])? $acc['cpanel_email']:'laptrinhweb123@gmail.com';

    //authorize
    $cpanel = new HW_CPanel($host, $cpaneluser, $cpaneluser_pass);
}
if(isset($cpanel)) {
    /**
     * upload file
     */
    if($task =='upload') {
        #$cpanel_file = HW_CPanel_Fileman::init($cpanel);
        $cpanel_file = HW_CPanel_Fileman::loadacct($acc_id);

        //start uploading file
        if(isset($_FILES['file'])) {
            //first upload file to temp folder
            $target_dir = dirname(__FILE__).'/tmp/';
            $target_file = $target_dir . basename($_FILES["file"]["name"]);
            $uploadOk = 1;

            // Check if file already exists
            if (file_exists($target_file)) {
                add_message( "Sorry, file already exists in /tmp folder.");
                $uploadOk = 0;
            }
            // Check file size
            if ($_FILES["file"]["size"] > 500000) {
                add_message ("Sorry, your file is too large.");
                $uploadOk = 0;
            }

            // if everything is ok, try to upload file
            if($uploadOk) {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                    add_message( "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.");
                } else {
                    add_message ("Sorry, there was an error uploading your file.");
                }
            }
            //start upload this file to host
            if(file_exists($target_file)) {
                $_FILES['file']['fullpath'] = $target_dir.$_FILES["file"]["name"];
                $res =$cpanel_file->uploadfiles($_FILES['file'], _post('upload_path'));
                add_message($res);
            }

        }
    }
    /**
     * write file
     */
    if($task == 'savefile') {
        //save file to this path
        $target_dir = _post('path');
        if(!$target_dir) $target_dir = _post('path1');

        #$cpanel_file = HW_CPanel_Fileman::init($cpanel);
        $cpanel_file = HW_CPanel_Fileman::loadacct($acc_id);

        $filedata = array(
            'dir'           => $target_dir,
            'file'          => _post('filename'),   //file with extension
            'content' => _post('content')
        );
        $res = $cpanel_file->savefile($filedata);
        add_message($res);
    }
}

invalid_form:
//list saved accounts
$accts = list_whm_accts();
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>File Manager (cPanel)</title>
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
    <table border="1px" cellpadding="5" cellspacing="0" width="">
        <tr>
            <td valign="top">
                <h2>File Manager (cPanel)</h2>

                <form method="post" id="myform_fileman" onsubmit="return myform_submit(this)">
                    <input type="hidden" name="task" value="savefile"/>
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
                            <fieldset><legend>Files/Folders</legend>
                                <a id="listftp" href="javascript:void(0)" onclick="hw_listfiles(this,'#myform_fileman','#listfiles_container')">List Files</a><br/>

                            <a id="" href="javascript:void(0)" onclick="hw_createfolder(this,$('#myform_fileman select[name=acc]').val() )">Create Folder</a><br/>
                            <a id="" href="javascript:void(0)" onclick="hw_fileman_emptytrash(this,$('#myform_fileman select[name=acc]').val() )">Empty Trash</a><br/>

                                </fieldset>

                            <fieldset><legend>Backup</legend>
                                <a id="listftp" title="List backup files in path /home/$user" href="javascript:void(0)" data-form="#myform_fileman" onclick="$('#myform_fileman').find('[name=path]').val('/public_html/hw_backups');$('#myform_fileman').find('[name=type]').val('zip');hw_listfiles(this,'#myform_fileman','#listfiles_container')">List backups</a><br/>
                                <hr/>
                                <a id="" title="Tạo backup lưu tại /home/$user/backup-{BACKUP-DATE_TIME}_{USER}.tar or backup-{BACKUP-DATE_TIME}_{USER}.tar.gz" class="tooltip" href="javascript:void(0)" onclick="hw_createNewBackup(this,$('#myform_fileman select[name=acc]').val() )">Create backup</a><br/>
                                <a id="" title="Create backup  cpmove-{USER},cpmove-{USER}.tar,cpmove-{USER}.tar.gz at /home" class="tooltip" href="javascript:void(0)" onclick="hw_createcpBackup(this,$('#myform_fileman select[name=acc]').val() )">Create cpbackup</a><br/>

                                <a id="" title="The files must be in one of the following directories on the server: /home, /home2, /home3, /root, /usr, /usr/home, or /web. Note: backup under the directory `/backup/cpbackup/monthly|weekly|daily`" class="tooltip" href="javascript:void(0)" onclick="hw_restorebackup(this,$('#myform_fileman select[name=acc]').val() )">Restore backup</a><br/>
                                <a id="" title="Restore cpbackup file created from `Create cpbackup`" class="tooltip" href="javascript:void(0)" onclick="hw_restore_cpbackup(this,$('#myform_fileman select[name=acc]').val() )">Restore cpbackup</a><br/>

                                <hr/>
                            <a id="" class="tooltip" href="javascript:void(0)" onclick="hw_setcronBackup(this,$('#myform_fileman select[name=acc]').val() )">Set cronjob backup</a><br/>
                                <a id="" class="tooltip" href="javascript:void(0)" onclick="hw_listcronsBackup(this,$('#myform_fileman select[name=acc]').val() ,'#listcrons_container')">List all crons</a><br/>

                                </fieldset>

                        </div>

                    </div>
                    </p>
                    <p>
                        <label for="path">Select Path</label><br/>
                        <select  name="path" >
                            <option value=""></option>
                            <option value="/public_html/hw_backups">Backups</option>
                            <option value="/public_html/wp-content/plugins">WP Plugins</option>
                            <option value="/public_html/wp-content/themes">WP Themes</option>
                        </select>(1)
                    </p>
                    <p>
                        <label for="path1">Enter Path</label><br/>
                        <input  name="path1" value="public_html/"/>(2)
                    </p>
                    <p>
                        <label for="type">type</label><br/>
                        <select  name="type" >
                            <option value="dir|file">All</option>
                            <option value="file">File</option>
                            <option value="dir">Folder</option>
                            <option value="zip">Zip/tar.gz</option>
                        </select><br/>
                        (<em>For 'List Files'</em>)
                    </p>
                    <fieldset><legend>Save File</legend>
                        <p>
                            <label for="filename">Filename</label><br/>
                            <input name="filename" type="text"/>
                        </p>
                        <p>
                            <label for="content">Save File Content</label><br/>
                            <textarea name="content" cols="100" rows="30"></textarea>
                        </p>
                    </fieldset>
                    <p>
                        <input type="submit" name="submit" value="Submit"/><!-- disabled="disabled"  -->
                    </p>
                </form>

            </td>
            <td valign="top">
                <h2>Upload (cPanel)</h2>
                <form method="post" enctype="multipart/form-data" id="myform_upload" onsubmit="return myform_submit(this)"><!-- enctype="multipart/form-data" -->
                    <input type="hidden" name="task" value="upload"/>
                    <!-- MAX_FILE_SIZE must precede the file input field -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />

                    <p>
                        <label for="acc">Accounts</label><br/>
                        <select name="acc" class="combobox_acc">
                            <option value="">-----select-----</option>
                            <?php foreach($accts as $row){
                                printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                            }?>
                        </select>
                    </p>
                    <fieldset><legend>Source</legend>
                    <p>
                        <label for="file">File</label><br/>
                        <input name="file" type="file" />
                    </p>
                    <p>
                        <label for="filepath">Source File path</label><br/>
                        <input type="text" name="filepath" value=""/>
                    </p>
                    </fieldset>

                    <p>
                        <label for="upload_path">Upload Path</label><br/>
                        <input  name="upload_path" value="/public_html/"/>
                    </p>
                    <p>
                        <input type="submit" name="submit" value="Upload"/>
                    </p>
                    <hr/>
                    <p>
                       <h3>Upload large file</h3>
                        <div>Note:
                            <ul>
                                <li>given 'Source File path' above to specific source.</li>
                                <li>given 'Upload path' where you want put on server.</li>
                            </ul>
                        </div>
                        <a href="javascript:void(0)" data-form="#myform_upload" onclick="hw_fileuploader(this, $('#myform_upload select[name=acc]').val())">Open Uploader</a>
                    </p>
                    <p>
                        <h3>Chmod folders & files</h3>
                    <div>
                        Note: nếu gặp lỗi 500 internal Error, có thể do các files/folders chưa chmod đúng. Thiết lập permission: <br>
                        folder: 755, files: 644

                    </div>
                        <div>
                            <label for="finddir">Find in folder</label><br/>
                            <input  name="finddir" value="public_html/"/>
                        </div>
                    <a href="javascript:void(0)" data-form="#myform_upload" onclick="hw_chmod755_644(this, $('#myform_upload select[name=acc]').val())">chmod all dir (755) & file(644)</a>
                    </p>
                </form>
            </td>
            <td valign="top">
                <div id="listfiles_container"></div>
                <div id="listcrons_container"></div>
            </td>
        </tr>
    </table>
        </div>
</div>
</body>
</html>