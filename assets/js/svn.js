/**
 * re-build svn_custom.conf
 * @param obj
 * @param frm
 */
function build_svn_custom_conf(obj, frm) {
    frm=$(frm);
    var acc = frm.find('[name=acc]:eq(0)').val(),//for hoangweb.vn
        subdomain = frm.find('[name=subdomain]:eq(0)').val();//subdomain: /

    //confirm
    if(confirm("Are you sure to update lastest svn_custom.conf file from server ?") ==false) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=build_svn_custom.conf&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: { subdomain: subdomain, allow_empty: 0},
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
 * create repository
 * @param obj
 * @param acc
 */
function hw_svn_createrepo(obj,frm) {
    frm=$(frm);
    var acc = frm.find('[name=acc]:eq(0)').val(),//for hoangweb.vn
        subdomain = frm.find('[name=subdomain]:eq(0)').val(),//subdomain: /
        repository_name= frm.find('[name=new_repo]:eq(0)').val(),   //new repository name will be create
        svn_user = frm.find('[name=svn_user]:eq(0)').val(); //svn user

    //exchange user for repository
    var force_bind_user2repo = confirm("Force to set user ["+svn_user+"] own repository ["+repository_name+"] ?");
    //valid
    if(!repository_name) {
        alert("Please Enter Repository Name at bellow.");
        frm.find('[name=new_repo]').focus();
        return;
    }
    if(jQuery.isNumeric(repository_name)) {
        alert("Repository name not allow nummeric.");
        return;
    }

    if(!confirm('Are you sure to create new repository ?')) return;
    if(prompt('Enter secure pass ?') !==hwcpanel.code) return ;
    var autopass= confirm('Enable password automatically?');

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_create_repo&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repository_name: repository_name, svn_user: svn_user, subdomain: subdomain,
            force_user2repo: force_bind_user2repo? 1:0, autopass: autopass ?1:0
        },
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
 * delete reponsitory from SVN subversion
 * @param obj
 * @param frm
 */
function hw_svn_delrepo(obj, frm) {
    frm=$(frm);
    var acc = $(obj).data('acc')? $(obj).data('acc') : frm.find('[name=acc]:eq(0)').val();
    var repo = $(obj).data('repository')? $(obj).data('repository') : (frm.find('[name=new_repo]:eq(0)').val()? frm.find('[name=new_repo]:eq(0)').val() : prompt("Enter repository name you want to remove ?") );

    if(!confirm('Are you sure to delete repository ['+repo+']?')) return;
    if(prompt('Enter secure pass ?') !==hwcpanel.code) return ;

    //valid
    if(!repo) {
        alert("No specific repository to remove ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_del_repo&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repository_name: repo,
            ssh_batch: 'svn-del-repo.bat',
            cmd : 'svn-del-repo.txt'   //in /commands folder
        },
        success: function(res) {
            log(res);

            if($(obj).parent().is('td') ) $(obj).closest('tr').remove();    //remove row if this link nest on cell of table
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
 * SVN dump repository
 * @param obj
 * @param frm
 */
function hw_svn_dumprepo(obj, frm) {
    frm=$(frm);
    var acc = frm.find('[name=acc]:eq(0)').val();
    var repo = prompt("Enter repository name you want to dump ?");

    if(!repo) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_dumprepo&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repository_name: repo,
            cmd: 'svn-dump-repo.txt'    //in /commands folder
        },
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
 * get demo link on the hoangweb.vn
 * @param obj
 */
function hw_svn_get_demo_link(obj) {
    var acc = $(obj).data('acc');
    var repo = $(obj).data('repository')? $(obj).data('repository') : prompt("Enter repository name you want to see demo ?");

    //valid
    if(!repo) {
        alert("No specific repository ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_get_demo_theme_link&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repository_name: repo
        },
        success: function(res) {
            log(res);
            if(res) window.open(res, '_blank');
            else alert("Parse demo link error.");

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
 * fix svn repository hooks
 * @param obj
 */
function hw_svn_fixsvn_hooks(obj, frm) {
    if(!confirm("Please confirm you checked right repository before want to fix their hooks ?")) return ;
    //get selected repositories
    var repo_list=hw_get_checkboxs_values(frm);

    var formdata = $(frm).serialize(),
        fixall = !repo_list? confirm("Fix all repositories ?"): 0,
        acc= $(obj).data('acc');    //account

    console.log(formdata);
    if(!confirm('Are you sure ?')) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=svn_fix_repository_hooks&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            fixall: fixall? 1:0,
            repositories: repo_list,
            cmd: 'svn-update-repository-hooks.txt'
        },
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
 * del zip files created when preview themes on website
 * @param obj
 * @param frm
 */
function hw_svn_del_demoszip(obj, frm) {
    var acc = $(obj).data('acc')? $(obj).data('acc') : frm.find('[name=acc]:eq(0)').val();
    //get selected repositories
    var repo_list=hw_get_checkboxs_values(frm),
        formdata = $(frm).serialize(),
        del_extract_folders = confirm('Also want to delete extract folders for you choose repositories ?'),
        delall = repo_list? 0: confirm('Do you want to del all ziped demo avaiable for repository on server ?');

    if(!repo_list && !delall) return;
    if(!confirm("Are you sure ?")) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=svn_del_repo_zips&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repositories: repo_list,
            cmd: 'svn-del-demo-zips-for-repository.txt',
            del_extract_folders: del_extract_folders? 1:0,
            delall: delall? 1:0
        },
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
 * delete extract folder that export from repository
 * @param obj
 * @param frm
 */
function hw_svn_delextract_repo(obj, frm) {
    frm=$(frm);
    var acc = $(obj).data('acc')? $(obj).data('acc') : frm.find('[name=acc]:eq(0)').val();
    var repo = $(obj).data('repository')? $(obj).data('repository') : (frm.find('[name=new_repo]:eq(0)').val()? frm.find('[name=new_repo]:eq(0)').val() : prompt("Enter repository name you want to remove ?") );

    if(!confirm('Are you sure to delete repository demo folder for ['+repo+']?')) return;
    if(prompt('Enter secure pass ?') !==hwcpanel.code) return ;

    //valid
    if(!repo) {
        alert("No specific repository to remove demo extracted folder ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_del_extractfiles_repo&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repository_name: repo
        },
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
 * lock/freeze repository
 * @param obj
 */
function hw_svn_freeze_repository(obj) {

    var acc = $(obj).data('acc');
    var repo = $(obj).data('repository')? $(obj).data('repository') : prompt("Enter repository name ?");
    var freeze_opt = confirm("Do you want to freeze repository ?");

    //valid
    if(!repo) {
        alert("No specific repository ?");
        return ;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_freeze_repository&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            repository_name: repo,
            freeze_mode: freeze_opt ? 1 :0
        },
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
 * list all repositories on server
 * @param obj
 * @param frm
 * @param holder
 */
function hw_svn_list_repositories(obj, frm, holder) {
    frm=$(frm);
    var acc = frm.find('[name=acc]:eq(0)').val();

    var update_repo_db = confirm("Do you want to update `svn_repositories` table ?");
    if(update_repo_db && prompt("Enter secure password ?") != hwcpanel.code) return ;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_list_repositories&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            update_repo_db: update_repo_db? 1:0
        },
        success: function(res) {
            //log(res);
            if(typeof holder == 'function') {
                holder(res);
            }
            else $(holder).html(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();

            var repositories_data= $('<div/>').append(res).find('textarea').val();
            $.ajax({
                url: 'ajax.php?do=svn_update_repositories_db&auth=1&acc='+acc,
                method:"POST",
                data: {repositories_update_rows: repositories_data},
                success: function() {
                    alert('Updated `svn_repositories` table.');
                }
            });
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * update all svn users from `svn_wp_users` table
 * @param obj
 * @param frm
 */
function hw_svn_update_all_wpusers(obj, acc) {
    if(!confirm('Update alll svn users passwd to server from localhost ?')) return;
    if(prompt('Enter secure password ?') !== hwcpanel.code) return;

    //var acc = $(frm).find('[name=acc]:eq(0)').val();

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=svn_update_users&auth=1&acc='+acc;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        data: {
            ssh_batch: 'svn-update-users-passwd.bat',
            cmd: 'svn-update-users-passwd.txt'
        },
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
 * add svn user
 * @param obj
 * @param frm
 */
function hw_svn_adduser(obj,frm) {
    frm=$(frm);
    var frmdata = frm.serialize(),  //form data
        acc= frm.find('[name=acc]:eq(0)').val(),
        frmdataArray = URLToArray(frmdata);

    //validation
    var require = ['svn_user', 'svn_user_pass'];
    if(!frmdataArray.svn_user || !frmdataArray.svn_user_pass) {
        alert('Required user and password.');
        if(!frmdataArray.svn_user) {
            frm.find('[name=svn_user]:eq(0)').focus();
            return;
        }
        if(!frmdataArray.svn_user_pass) {
            frm.find('[name=svn_user_pass]:eq(0)').focus();
            return;
        }
    }
    frmdataArray['update_svn_user'] = 1;    //also update user to `svn_wp_users` & `svn_repositories` table
    //create wp user
    hw_create_wpuser(obj,acc, function(result) {
        alert("Chú ý: Không cần cập nhật lại bảng mysql `svn_wp_users` & `svn_repositories` lên server, wp user này đã được đồng bộ với `svn_wp_users` +`svn_repositories`.");
        //bellow code do not need because hw_create_wpuser already add svn user to local db.
        if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
            alert('working..');
            return;
        }
        var url = 'ajax.php?do=add_svn_user&auth=1&acc='+acc ;
        $.ajax({
            url: url,
            //dataType: 'json',
            method: 'POST',
            data: frmdataArray,
            success: function(res) {
                log(res);
                frm[0].reset(); //clear all form fields after ajax completion

                //$(obj).hw_remove_loadingImage();
                $(obj).hw_reset_ajax_state();
            },
            error: function(err){
                console.log("Error:",err);
                $(obj).hw_reset_ajax_state();
            }
        });

    }, {
        user: frmdataArray.svn_user,
        pass: frmdataArray.svn_user_pass,
        email: frmdataArray.svn_user_email,
        savedb: 1,
        fullname: frmdataArray.svn_fullname,
        update_svn_user: 1,
        role: 'contributor',
        login_path: '/quanly/dangnhap'
    });


}
/**
 * delete svn user
 * @param obj
 * @param acc
 */
function hw_svn_deluser(obj, acc) {
    var frm = $($(obj).data('form')),
        user = frm.find('[name=svn_user]:eq(0)').val();

    if(!confirm('Delete svn wp user ['+user+'] ?')){
        return;
    }
    if(prompt('Enter secure password ?') !== hwcpanel.code) return;
    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }

    var url = 'ajax.php?do=svn_del_user&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {svn_user: user, update_svn_user: 1},
        success: function(res) {
            log(res);

            //$(obj).hw_remove_loadingImage();
            $(obj).hw_reset_ajax_state();
            alert('Please refresh this page !');
        },
        error: function(err){
            console.log("Error:",err);
            $(obj).hw_reset_ajax_state();
        }
    });
}
/**
 * list svn users
 * @param obj
 * @param acc account for svn website
 */
function hw_svn_update_list_wpusers(obj, acc) {
    if(!confirm('Update svn wp users list from hoangweb.vn into `svn_wp_users` in this application ?')){
        return;
    }
    if(prompt('Enter secure password ?') !== hwcpanel.code) return;

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var frm = $($(obj).data('form')),
        subdomain = frm.find('[name=subdomain]:eq(0)').val();

    var url = 'ajax.php?do=svn_updatelist_users&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {},
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
 * export
 * @param obj
 * @param acc
 */
function hw_svn_export_sql_users(obj, acc) {
    if(!confirm('Export `svn_wp_users` & `svn_repositories` table from localhost database. ?')){
        return;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var frm = $($(obj).data('form')),
        subdomain = frm.find('[name=subdomain]:eq(0)').val();

    var url = 'ajax.php?do=export_localdb&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {
            file:'svn-data', include_tables: 'svn_wp_users,svn_repositories', savepath: 'tmp/',
            subdomain: subdomain
        },
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
 * import tmp/svn-data.sql
 * @param obj
 * @param acc
 */
function hw_svn_import_svn_users(obj,acc) {
    var frm= $($(obj).data('form')),
        subdomain = frm.find('[name=subdomain]:eq(0)').val();

    if(!confirm('Import `tmp/svn-data.sql` on server. Are you sure ?')){
        return;
    }

    if($(obj).hw_is_ajax_working({loadingText:'loading..'})) {
        alert('working..');
        return;
    }
    var url = 'ajax.php?do=import_db&auth=1&acc='+acc ;
    $.ajax({
        url: url,
        //dataType: 'json',
        method: 'POST',
        type: "POST",
        data: {file: __hwcpanel.ROOT_DIR +'/tmp/svn-data.sql', subdomain: subdomain
            //dbname: dbname    //get dbname from DB_NAME -> wp-config.php
        },
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
 * open URL in new window
 * @param url
 */
function hw_svn_openUrl(url) {
    var repo= "http://svn.hoangweb.vn/themes/"+url;
    var win = window.open(repo, '_blank');
}