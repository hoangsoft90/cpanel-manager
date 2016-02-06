/**
 * reset wp admin
 * @param obj
 * @param acc
 * @param holder
 */
function hw_reset_wpadmin(obj,acc, holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    var frm= $($(obj).data('form')),
        frmdata = frm? URLToArray(frm.serialize()) : {};

    //valid
    if(!frmdata.subdomain && $(obj).data('subdomain')) {
        frmdata.subdomain = $(obj).data('subdomain');
    }

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    var send_mail = confirm('Is Send mail to website owner ?');
    var user = $(obj).data('user')? $(obj).data('user') : prompt('Enter user you want to reset password ?', 'admin');   //select user
    var pass = prompt('Enter new password for ['+user+']?', generate_password(20));
    var savedb = confirm('Want to save wp acc to db ?');    //save to db option

    //confirm
    if(!confirm('Reset WP account for ['+user+'] ?')) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=reset_wpadmin&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            user: user, pass: /*encodeURIComponent*/(pass),subdomain: subdomain,sendmail: send_mail,
            savedb: savedb? 1:0
        },
        success: function(res) {
            log(res);
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
 * create wp user
 * @param obj
 * @param acc
 * @param holder
 * @param args
 */
function hw_create_wpuser(obj,acc, holder,args) {

    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    //first valid
    if(!acc) {
        alert('Please select account ?');
        return;
    }
    var user = args && args.user? args.user : prompt("Enter username ?"),
        pass = args && args.pass? args.pass : prompt("Enter user pass ?", generate_password(20)),
        email = args && args.email? args.email : prompt("Enter user email ?", 'hoangsoft90@gmail.com'),
        role = args && args.role? args.role : prompt("Enter user role ?", 'administrator'),
        savedb = args && args.savedb? args.savedb : confirm('Do you want to save wp acc to database ?'),
        fullname = args && args.fullname? args.fullname : '',
        update_svn_user = args && args.update_svn_user? args.update_svn_user : 0,
        login_path = args && args.login_path? args.login_path : '';

    //confirm
    if(!confirm('Are you sure to create new WP User ['+user+'] ?')) return;

    //valid
    if(!user || !pass) {
        alert('Sory, Empty user or pass.');
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=create_wp_user&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: 'POST',
        data: {
            user: user, pass: pass,email:email,role: role, subdomain:subdomain,
            savedb: savedb? 1:0, fullname: fullname, update_svn_user: update_svn_user,
            login_path: login_path
        },
        success: function(res) {
            log(res);
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
 * list wp users
 * @param obj
 * @param acc
 * @param holder
 */
function hw_list_wpusers(obj, acc,holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';


    var url = 'ajax.php?do=list_wp_user&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain},
        success: function(res) {
            //log(res);
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
 * list all wp users roles
 * @param obj
 * @param acc
 * @param holder
 */
function hw_list_wpusers_roles (obj, acc, holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    var url = 'ajax.php?do=list_wp_user_roles&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain},
        success: function(res) {
            //log(res);
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
 * update wp-config.php
 * @param obj
 * @param acc
 */
function hw_update_wpconfig(obj,acc) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    //valid
    if(!frmdata.dbname) {
        //make sure you see dbs list for current acct and pick one of db ID for wp-config.php
        alert('Please Click on List databases before ?');
        return;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    var dbid = prompt('Enter ID to determine DB User info that you see on current page ?');
    if(!jQuery.isNumeric(dbid)) return ;

    var url = 'ajax.php?do=update_wpconfig&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain, dbid: dbid},
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
/**
 * list all activation plugins in wp site
 * @param obj
 * @param acc
 * @param holder
 */
function hw_wp_list_plugins(obj, acc, holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=wp_list_plugins&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain },
        success: function(res) {
            //log(res);
            if(typeof holder =='function') holder(res);
            else $(holder).html(res);

            //sortable items of output
            $( "#sortable" ).sortable({
                //when drop item, remember the .index() value is zero-based, so you may want to +1 for display purposes.
                stop: function(event, ui) {
                    console.log("Start position: " + ui.item.startPos);
                    //alert("New position: " + ui.item.index());

                    var idsInOrder = $("#sortable"). sortable('toArray'); //return ids of element sorted
console.log(idsInOrder);
                    //If you want to find the position of a particular one, use $.inArray(), like this:
                    //var index = $.inArray("idToLookFor", idsInOrder);
                }
            });
            $( "#sortable" ).disableSelection();

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
 * active plugins
 * @param obj
 * @param acc
 * @param holder
 */
function hw_wp_active_plugins(obj, acc,holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var addlist = prompt('Enter list of active plugins spearate by comma ?');

    var url = 'ajax.php?do=wp_active_plugins&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain , addlist: addlist},
        success: function(res) {
            log(res);
            if(typeof holder =='function') holder(res);
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
 * deactive wp plugins
 * @param obj
 * @param acc
 * @param holder
 */
function hw_wp_deactive_plugins(obj, acc, holder) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var rm_all = confirm('Do you want to deactive all plugins ?'),
        rm_plugins = prompt('Enter list of deactive plugins separate by comma ?');

    var url = 'ajax.php?do=wp_deactive_plugins&auth=1&acc='+acc+ '&rm_all='+rm_all;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain, rm_plugins:rm_plugins},
        success: function(res) {
            log(res);

            if(typeof holder =='function') holder(res);
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
 * enable wp debug
 * @param obj
 * @param acc
 * @param holder
 */
function hw_enable_wpdebug(obj, acc, holder) {
    var frm= $($(obj).data('form')),
        frmdata = URLToArray(frm.serialize());

    var upload_path = '/public_html/'+frmdata.subdomain,
        subdomain = frmdata.subdomain? frmdata.subdomain : '';

    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=enable_wpdebug&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain},
        success: function(res) {
            log(res);
            if(typeof holder =='function') holder(res);
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
 * save order of avaiable wp plugins
 * @param obj
 * @param acc
 * @param sortable
 */
function hw_saveorder_wpplugins(obj, acc, sortable) {
    var url = 'ajax.php?do=saveorder_wpplugins&auth=1&acc='+acc;
    var items = $(sortable).find('li.ui-state-default').data('item');console.log(items);
    var subdomain = $(obj).data('subdomain');
    /*for(var i in items) {
        items[i];
    }*/
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain, plugins: items},
        success: function(res) {
            log(res);
            if(typeof holder =='function') holder(res);
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
 * delete wp user
 * @param obj
 * @param user_id
 * @param holder
 */
function hw_delete_wpuser(obj,acc,user_id, holder) {
    //confirm
    if(!confirm('Delete WP User ['+user_id+'] ?')) return;
    if(prompt('Enter your pass ?') !== hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var subdomain = $(obj).data('subdomain');

    var url ='ajax.php?do=del_wpuser&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain, user_id: user_id},
        success: function(res) {
            log(res);
            if(typeof holder =='function') holder(res);
            else $(holder).html(res);

            //remove row
            $(obj).closest('tr').remove();

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
 * update wp user role
 * @param obj
 * @param acc
 * @param user_id
 * @param holder
 */
function hw_wp_update_user_role(obj,acc,user_id, holder) {
    var subdomain = $(obj).data('subdomain');
    var role = prompt('Enter new role for user '+user_id+'?') ;
    //confirm
    if(prompt("Enter secure password ?") !== hwcpanel.code) {
        return;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url ='ajax.php?do=update_wpuser_role&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {subdomain:subdomain, user_id: user_id, role: role},
        success: function(res) {
            log(res);
            if(typeof holder =='function') holder(res);
            else $(holder).html(res);

            //remove row
            $(obj).closest('tr').css({'background':'red',color:'#fff'});

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
 * install wordpress core on server
 * @param obj
 * @param acc
 */
function hw_install_wp(obj,acc) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }

    if( !confirm("Upload Wordpress on the account ? ")) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=install_wp&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
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
 * turn wordpress to maintenance mode
 * @param obj
 * @param acc
 */
function hw_wp_maintenance(obj, acc) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var open_website = confirm('Do you want to unlock website ?'),
        redirect = open_website? '': prompt('Enter URL to redirect to when access website ?', 'http://hoangweb.com');

    var url = 'ajax.php?do=wp_maintenance&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data:{url: redirect, unlock: open_website? 1:0},
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
 * fixed base url in database (mysql)
 * @param obj
 * @param acc
 */
function hw_wp_fixedbaseurl_mysql(obj, acc) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    var frm = $($(obj).data('form')),
        db = frm.find('[name=dbname]:eq(0)').val(),
        dbuser = frm.find('[name=dbuser]:eq(0)').val(),

        findstr =frm.find('[name=findstr]:eq(0)').val(),
        replacestr = frm.find('[name=replstr]:eq(0)').val();

    if( !confirm("Already to fix base url in mysql database ? ")) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=wp_sql_fixedbaseurl&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',type: "POST",
        data:{cmd: 'wp_fixedbaseurl_mysql.txt', findstr: findstr, replacestr: replacestr,db:db, dbuser: dbuser},
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
 * install some require plugins & also make configuration
 * @param obj
 * @param acc
 */
function hw_wp_plugins_configuration(obj, acc) {
    //valid
    if(!acc) {
        alert("Please select account ?");
        return ;
    }
    //confirm
    if(!confirm("Are you sure ?")) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=wp_import_plugins_setting&auth=1&acc='+ acc;
    $.ajax({
        url: url,
        data: {
            acc: acc, cmd: 'wp_import_plugins_setting.txt'
        },
        method: 'POST',type: "POST",
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