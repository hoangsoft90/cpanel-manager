/**
 * list ftp from account
 * @param obj
 * @param acc
 * @param holder
 */
function hw_listftp(obj, acc, holder) {
    if(!acc) {
        alert("Select account ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=listftp_acct&auth=1&acc='+acc;
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
 * list ftp sessions
 * @param obj
 * @param acc
 * @param holder
 */
function hw_listftp_sessions(obj, acc, holder) {
    if(!acc) {
        alert("Select account ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=listftp_sessions&auth=1&acc='+acc;
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
 * kill a ftp session
 * @param obj
 * @param acc
 * @param pid process ID for ftp session
 */
function hw_kill_ftpsession(obj, acc, pid) {
//valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    if(!pid) {
        alert('Not found PID for ftp session.');
        return;
    }
    if( !confirm("Are you sure to kill ftp session has PID="+pid+" ? ")) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=kill_ftp_session&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data:{cmd: 'kill-ftp-session.txt', pid: pid},
        success: function(res) {
            log(res);
            $(obj).closest('tr').remove();  //remove table row
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
 * set ftp account
 * @param obj
 */
function hw_setftp_pass(obj,acc,ftpuser ) {
    if(!confirm('set ftp account ?')) {
        return;
    }
    if(!ftpuser) ftpuser = prompt('ftp account user ?');

    var user = ftpuser;
    var pass = prompt('ftp account pass for ['+user+']?', generate_password(20));
    //validation
    if(!user || !pass) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=setftp_pass&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'post',
        data : {user: ftpuser, pass: pass},
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
 * del ftp account
 * @param obj
 * @param acc
 * @param ftpuser
 */
function hw_delftp(obj, acc, ftpuser) {
    if(!confirm('delete ftp account ['+ftpuser+']?')) {
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=delftp_acct&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'post',
        data : {user: ftpuser},
        success: function(res) {
            log(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();

            $($(obj).closest('tr')).remove();   //remove current row
        },
        error: function(err){
            console.log("Error:", err);
            $(obj).hw_reset_ajax_state();
        }
    });
}