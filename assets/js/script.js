if(typeof hwcpanel =='undefined'){
    var hwcpanel = {};
}
hwcpanel.loading_image = 'http://localhost/cpanel/assets/images/loading1.gif';
hwcpanel.code = '837939';

/**
 * delete account from manager
 */
function hw_del_account_fromdb(user,id){
    if(!confirm('Do you want to delete account ['+user+']')) {
        return ;
    }
    if(prompt('Enter secure password ?') !== hwcpanel.code) return ;
    location.href='?id='+id+'&do=delete';
}
/**
 * list all cpanels acc
 * @param obj
 * @param holder
 */
function hw_cp_list_all(obj,holder) {
    var update_list = confirm('Update cpanels manager ?') ;
    //var pass = prompt('Enter secure password ?');
    //if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=listcp_accts&auth=1&update_list='+update_list;
    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(res);
            }
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();

            //if(update_list) window.location.reload();   //refresh list
            if(update_list) alert('please reload this page.');
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}

/**
 * delete cpanel account
 * @param usrname
 */
function hw_cp_del(obj,usrname) {
    var domain = $(obj).data('domain'),
        host = $(obj).data('host');

    //confirm
    if(!confirm('Do you want to delete account ['+usrname+'] ?')) return;

    //confirm text
    var confirm_str = prompt("Enter 'DELETE' ? to confirm your task.", "");
    if(confirm_str !== 'DELETE') return ;

    //enter password
    var confirm_pass = prompt("Enter your password.", "");
    if(confirm_pass !== hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    $(obj).closest('tr').css({'background-color':'pink'});

    var url = 'ajax.php?do=delacct&auth=1&cpuser='+ usrname;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{cp_domain: domain, cp_host: host},
        success: function(res) {
            log(res);
            $($(obj).closest('tr')).remove();
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * set user password
 * @param obj
 * @param username
 */
function hw_cp_setpass(obj, username) {
    //confirm
    if(!confirm('Do you want to reset pass for ['+username+'] ?')) return;

    //enter password
    var confirm_pass = prompt("Enter your password.", '');
    if(confirm_pass !== hwcpanel.code) return;

    var pass = prompt("Enter new password for ["+username+"] ?", generate_password(20));

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    $(obj).closest('tr').css({'background-color':'pink'});

    var url = 'ajax.php?do=setacctpass&auth=1&cpuser='+ username;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {pass: pass},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * valid acct
 * @param id
 */
function hw_testacct_connection(obj,id) {
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=testacct&auth=1&id='+ id;
    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * generate cpanel token by root access
 * @param obj
 * @param id
 */
function hw_create_accttoken(obj,id) {
    if( !confirm("refresh token for this user cpanel? ",1)) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=create_cpuser_token&auth=1&id='+ id;
    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * save cpanel to db
 * @param obj
 */
function hw_save_cpacct(obj) {
    if( !confirm("Save this new cpanel ? ")) return;
    var row=$(obj).closest('tr');
    var domain = $(obj).data('domain'),
        ip = $(obj).data('ip'),
        user = $(obj).data('user'),
        email = $(obj).data('email');

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=saveacct&auth=1' ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{domain:domain, ip: ip, user: user, email: email},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * enable ssh access for the cpanel from whm
 * @param obj
 * @param acc
 */
function hw_enable_sshacct(obj, acc) {
    if( !confirm("Enable ssh access for the account ? ")) return ;
    if(prompt('Enter secure password ?') !== hwcpanel.code) return ;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=modifyacct&auth=1&acc='+ acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{HASSHELL:'1',hasshell:'1'},    //note distingish case intensive
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * generate ssh key
 * @param obj
 * @param acc
 */
function hw_generate_sshkey(obj, acc) {
    if( !confirm("Generate ssh access key for the account ? ")) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=generate_sshkey&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * generate ssh key & download all ssh keys at /.ssh/ folder on server in ssh-keys directory
 * @param obj
 * @param acc
 */
function hw_complete_generate_sshkey(obj, acc) {
    if( !confirm("Generate ssh access key for the account ? ")) return;
    /*
    window.open('cmd.exe /c E:/HoangData/HoangWeb/projects/whm-cpanel-shell/x_ssh/__gen-sshkey.bat -acc '+acc);

    if( navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ){
        // Do something in Firefox
        var file = Components.classes["@mozilla.org/file/local;1"].createInstance(Components.interfaces.nsILocalFile);
        file.initWithPath("C:\\Windows\\System32\\cmd.exe");
        file.launch();
    }
    if( navigator.userAgent.toLowerCase().indexOf('chrome') > -1 ){
        // Do something in Chrome
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }*/
    var url = 'ajax.php?do=generate_sshkey_batch&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * clear spool exim on server to prevent 500 internal error when exceed disk
 * @param obj
 */
function hw_reset_spoolexim(obj) {
    if(!confirm('Reset spool exim on server ?')) return;

    var url = 'ajax.php?do=clear_spoolexim&auth=1' ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{
            cmd: 'clear_spoolexim',
            ssh_batch: 'clear_spoolexim.bat'
        },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * test ssh connection
 * @param obj
 * @param acc
 */
function hw_test_ssh_connection(obj, acc) {
    var url = 'ajax.php?do=test_sshconnection&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
//-------------------------------------FTP---------------------------------------------
/**
 * list files
 * @param obj
 * @param acc
 * @param holder
 */
function hw_listfiles(obj, frm, holder) {
    frm = $(frm);   //form object
    //valid
    if(!frm.serializeArray()[1].value) {
        alert("Select one account ?");
        return;
    }
    //set focus selectbox item
    //$(frm).find('[name=path]').val('/public_html/hw_backups');
    //$(frm).find('[name=type]').val('zip');

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=listfiles&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: $(frm).serializeArray(),
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(res);
            }
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * extract file
 * @param obj
 * @param file
 */
function hw_extractfile(obj, file,path) {
    //valid
    if(!confirm('Extract now ?')) {
        return;
    }
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    var acc = $(obj).data('cpid');

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=extractfile&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {file: file, path: path ,acc: acc},
        success: function(res) {
            log(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * delete file
 * @param obj
 */
function hw_delfile(obj, file,path) {
//valid
    if(!confirm('Delete this file ['+ file +']?')) {
        return;
    }
    var confirm_task = prompt('Enter your password ?');
    if(confirm_task != hwcpanel.code) return;

    var acc = $(obj).data('cpid');

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=delfile&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {file: file, path: path ,acc: acc},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
            if(res != 'Error.') {
                $($(obj).closest('tr')).remove();
            }

        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * create folder
 * @param obj
 */
function hw_createfolder(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }

    var folder = prompt('Enter full path ?');
    var chmod = prompt('chmod for this folder?');

    //valid
    if(!folder) return;

    //parse
    folder = folder.replace(/^[\/\,\s]+|[\/\,\s]+$/g,'');
    var x= folder.split(/[\\|\/]/g);
    var name = x.pop(),
        path = x.join('/');
console.log(name,path);

    //confirm
    if(!confirm('Do you want to continue ?')) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=createdir&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {path: path, name: name ,acc: acc},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * empty trash folder
 * deletes the contents of the user's /trash directory.
 * @param obj
 * @param acc
 */
function hw_fileman_emptytrash(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    //confirm
    if(!confirm('Do you want to continue ?')) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=empty_trash&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {path: path, name: name ,acc: acc},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * create backup
 * @param obj
 * @param acc
 */
function hw_createNewBackup(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }

    if(!confirm('Do you want a new backup ? store in home/{$user}/ folder.')) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=createbackup&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {
            saveto: 'public_html/hw_backups' ,  //no
            acc: acc
        },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * create cpbackup generate file cpmove-{USER},cpmove-{USER}.tar,cpmove-{USER}.tar.gz
 * @param obj
 * @param acc
 */
function hw_createcpBackup(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    var clone2acc='';
    if(confirm('Clone to account ?')) {
        clone2acc= prompt('Enter user for account you want to copy backup to ?, ie: hwvn');
    }

    if(!confirm('Want to create cpbackup file ?')) return;
    if(prompt('Enter secure password ?') !== hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=create_cpbackup&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {
            acc: acc, clone2acc:clone2acc
        },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * restore backup
 * @param obj
 * @param acc
 */
function hw_restorebackup(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    //confirm
    if(! confirm('Are you sure to restore this cpanel ? Remember to select account you want to restore.')) return ;
    if(prompt('Enter secure password ?') !== hwcpanel.code) return ;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=restore_backup&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {
            acc: acc
        },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * restore cpbackup file
 * @param obj
 * @param acc
 */
function hw_restore_cpbackup(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    //confirm
    if(! confirm('Are you sure to restore this cpanel ? Remember to select account you want to restore.')) return ;
    if(prompt('Enter secure password ?') !== hwcpanel.code) return ;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=restore_cpbackup&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {
            acc: acc
        },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * set cron job backup
 * @param obj
 * @param acc
 */
function hw_setcronBackup(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if(!confirm('Do you want to set cron backup for this account ?')) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=add_cronbackup&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {
            //command:'/usr/bin/php -q http://hoangweb.vn/apps/cpanel/cron_backup2.php', //for cpanel acct
            command:'curl -d "user=huy&pass=pass" http://hoangweb.vn/apps/cpanel/cron_backup2.php?acc='+acc, //for cpanel acct
            day:'1',
            hour:'1',
            minute:'1',
            month: '1',
            weekday: '1',
            acc: acc
        },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * list crons job for backup
 */
function hw_listcronsBackup(obj,acc, holder) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=listcronsbackup&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        data: {
            acc: acc
        },
        success: function(res) {
            //log(res);
            //$(obj).hw_remove_loadingImage();
            if(typeof holder == 'function') holder(res);
            else $(holder).html(res);

            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * remove cron job
 * @param obj
 * @param acc
 */
function hw_delcron(obj,id) {
    //get acct id
    var acc = $(obj).data('cpid');
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if(!confirm('Delete cron with ID='+id)) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=delcron&auth=1&acc='+acc+'&id='+ id;
    $.ajax({
        url: url,
        //dataType: 'json',
        data: {
            acc: acc
        },
        success: function(res) {
            //log(res);
            $(obj).hw_reset_ajax_state();
            //$(obj).hw_remove_loadingImage();
            $($(obj).closest('tr')).remove();   //remove line

        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * move file/folder
 * @param obj
 * @param file
 * @Param acc
 */
function hw_moveobj(obj,file, acc) {
    if(!acc) acc = $(obj).data('cpid');
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if(!confirm('Move this file ['+file+'] ?')) return;

    var target= prompt('Specific path for this file ['+file+'] to located ?', '/public_html/');
    //var mvfiles = (confirm('Move all files in folder ?'));

    //parse
    /*if(mvfiles) {
        file += '/*';
    }*/

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=performfiles&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {file: file ,movedir: target,op:'move'},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * download file
 * @param obj
 * @param file
 * @param acc
 */
function hw_downloadfile(obj,file,acc) {
    if(!acc) acc = $(obj).data('cpid');
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=downloadfile&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {file: file },
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * transfer backup compressed file to other server
 * @param obj
 */
function hw_transfer_backup_4other_acct(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    var file = $(obj).data('file');
    var target = prompt('Enter target account ID you want to transfer backup file ?');
    var rename_userfile = confirm("Rename $user in backup file belong to user' other server ?");

    //confirm
    if(!confirm('Are you sure to transfer this file ?')) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=transfer_file&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {file: file ,target_acc : target, rename_userfile: rename_userfile?'1':'0'},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * open file uploader
 * @param obj
 * @param acc
 */
function hw_fileuploader(obj,acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    var frm = $(obj).data('form'),
        filepath= $(frm).find('[name=filepath]:eq(0)').val(),    //specific source
        upload_path = $(frm).find('[name=upload_path]:eq(0)').val();    //upload to this path

    //valid
    if(!filepath || !upload_path) {
        alert("Specific source & destination ? ");
        return;
    }
    var root_access = confirm('Need Root access (WHM) ?');
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=open_fileuploader&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {root_access: root_access?1:0, filepath:filepath, upload_path:upload_path},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * chmod all directory to 755 and files to 644
 * @param obj
 * @param acc
 */
function hw_chmod755_644(obj, acc) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if(!confirm('Are you sure to chmod ?')) return;
    var frm=$($(obj).data('form')),
        dir = frm.find('[name=finddir]:eq(0)').val();   //chmod to this dir

    var url = 'ajax.php?do=chmod&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method : "POST",
        data: {dir: dir},
        success: function(res) {
            log(res);
            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
//-------------------------------------DB---------------------------------------------
/**
 * list saved dbs for acct
 * @param obj
 * @param acc
 * @param holder
 */
function hw_listsaved_dbs(obj,acc,holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=listsaved_acc_dbs&auth=1&acc='+acc;

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(frm,res);
            }
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * list dbs for account
 * @param acc
 * @param holder
 */
function hw_list_dbs(obj, acc,holder,json) {
    //$(obj).hw_set_loadingImage();
    var frm = $(obj).data('form');
    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=listacc_dbs&auth=1&acc='+acc+'&json='+ (json?1:0);

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(frm,res);
            }
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * list mysql table data
 * @param obj
 * @param acc
 * @param holder
 */
function hw_list_dbtable_data(obj, acc, holder, args) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }

    var table = args && args.table? args.table : prompt("Enter table name want to fetch data ?", "");

    if(!table) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=listacc_dbtable_data&auth=1&acc='+acc;

    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {table: table},
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(frm,res);
            }
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * delete db from manager
 * @param obj
 * @param id
 */
function hw_deldb_fromlocal(obj,id) {
    //confirm
    if(!confirm('Are you sure want to del local DB with ID=['+id+'] ?')) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=deldb_from_manager&id='+id;
    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            //log(res);
            $($(obj).closest('tr')).remove();   //remove current row

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * delete db from cpanel account
 * @param db
 */
function hw_deldb(obj,acc,db) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    //confirm
    if(!confirm('Do you want to delete db ['+db+'] ?')) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=deldb&acc='+acc+'&db='+db;

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);
            $($(obj).parent()).hide();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:" ,err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * check database
 * @param obj
 * @param acc
 * @param db
 */
function hw_checkdb(obj,acc,db) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=checkdb&acc='+acc+'&db='+db;

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}

//---------------------------------------------------DB user--------------------------------------------------
/**
 * del user db
 * @param obj
 * @param acc
 * @param user
 * @param db
 */
function hw_deluserdb(obj,acc,user,db) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }

    var db = prompt("db name?", "");
    if(!db) return;
//confirm
    if(!confirm('Do you want to delete user db [db:'+db+',user:'+user+'] ?')) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=deluserdb&acc='+acc+'&user='+user+'&db='+db;

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);
            $($(obj).parent()).hide();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * del db user
 * @param obj
 * @param acc
 * @param user
 */
function hw_deluser(obj,acc,user) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }

    //confirm
    if(!confirm('Do you want to delete user ['+user+'] ?')) return;
    var pass = prompt('Enter secure password ?');
    if(pass != hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=deluser&acc='+acc+'&user='+user;

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);
            $($(obj).parent()).hide();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * set user password
 * @param obj
 * @param acc
 * @param user
 */
function hw_setuserpass(obj,acc,user) {
    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    //confirm
    if(!confirm('Update pass for user ['+user+'] ?')) return;
    var pass = prompt('Enter new pass for user ['+user+'] ?', generate_password());

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=setuserpass&acc='+acc+'&user='+user;

    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'post',
        data: {user: user, pass: pass},
        success: function(res) {
            log(res);
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * list acc' users
 * @param obj
 * @param acc
 * @param holder
 */
function hw_list_users(obj, acc,holder,json) {
    var frm = $(obj).data('form');
    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=listacc_users&auth=1&acc='+acc+'&json='+ (json?1:0);

    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            console.log(res);
            if(typeof holder == 'function') holder(frm,res);
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * open root ssh terminal
 */
function hw_openssh_root(obj) {
    var url = 'ajax.php?do=openssh_rootaccess&auth=1';
    $.ajax({
        url: url,
        //dataType: 'json',
        success: function(res) {
            log(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}

//----------------------------------------domain-----------------------------------------
/**
 * create a subdomain
 * @param obj
 * @param acc
 */
function hw_create_subdomain(obj, acc, holder) {
    var frm = $($(obj).data('form')),
        formData = URLToArray(frm.serialize()),
        subdomain = $(obj).data('subdomain')? $(obj).data('subdomain') : formData.subdomain;

    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }
    //confirm
    if(!subdomain) {
        alert('Please enter subdomain name ?');
        frm.find('[name=subdomain]').focus();
        return ;
    }
    if(!confirm('Do you want to create subdomain  ['+subdomain+']?')) return;
    if(prompt("Enter secure pass ?") !== hwcpanel.code) return ;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=create_subdomain&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data : frm.serialize(), //pass form data
        success: function(res) {
            log(res);
            if(typeof holder == 'function') holder(frm,res);
            else if(holder) $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * delete a subdomain
 * @param obj
 * @param acc
 * @param holder
 */
function hw_del_subdomain(obj, acc, holder) {
    var frm = $($(obj).data('form')),
        formData = URLToArray(frm.serialize()),
        subdomain = $(obj).data('subdomain')? $(obj).data('subdomain') : formData.subdomain;

    if(!subdomain) subdomain = prompt("Enter subdomain you want to delete ?", subdomain);
    var  url = 'ajax.php?do=del_subdomain&auth=1&acc='+acc;

    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }
    //confirm
    if(!subdomain) {
        alert('Please enter subdomain name ?');
        frm.find('[name=subdomain]').focus();
        return ;
    }
    if(!confirm('Do you want to del subdomain ['+subdomain+'] ?')) return;
    if(prompt("Enter secure pass ?") !== hwcpanel.code) return ;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data : {subdomain : subdomain}, //pass form data
        success: function(res) {
            log(res);
            if(typeof holder == 'function') holder(frm,res);
            else if(holder) $(holder).html(res);

            //del table row
            if($(obj).parent().is('td') ) $(obj).closest('tr').remove();

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * list all subdomains from an account
 * @param obj
 * @param acc
 * @param holder
 */
function hw_list_subdomains(obj, acc, holder) {
    var frm = $($(obj).data('form'));
    //valid
    if(!acc) {
        alert("Please select account ?");
        return;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=list_subdomains&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data : {}, //pass form data
        success: function(res) {
            console.log(res);
            if(typeof holder == 'function') holder(frm,res);
            else if(holder) $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * export database
 * @param obj
 * @param acc
 */
function hw_exportdb(obj, acc,holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    //confirm
    if(!confirm('Are you sure ?')) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    var url ='ajax.php?do=export_db&auth=1&acc='+acc,
        filename = prompt('Enter sql file name for exporting ?');

    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain, file: filename},
        success: function(res) {
            log(res);

            if(typeof holder =='function') holder(res);
            else $(holder).html(res);

            res = JSON.parse(res);console.log(res);
            //open download file
            if(res[2]) {
                var link = res[2].link;
                OpenInNewTab(link);
            }

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();

        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * import db
 * @param obj
 * @param acc
 */
function hw_importdb(obj, acc, holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var subdomain = frmdata.subdomain? frmdata.subdomain : '',
        file = frmdata.file? frmdata.file : '', //upload this file
        db = prompt("DB name you want to import ? leave empty for current db.\nNote: enter full db name include prefix, ie: hwvn_db1");

    //confirm
    if(!confirm('Are you sure ?')) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url ='ajax.php?do=import_db&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain : subdomain ,file:file, dbname: db},
        success: function(res) {
            log(res);

            if(typeof holder == 'function') holder(res);
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();

        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
