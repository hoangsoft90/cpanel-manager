<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/09/2015
 * Time: 17:08
 */
#-------------------------------------------------------------------------------------------------------
#   SVN Users
#-------------------------------------------------------------------------------------------------------
/**
 * add new svn user
 * @param array $data
 * @param $id user id to edit
 */
function hw_svn_adduser($data= array(), $id='') {
    global $DB;
    $fields ='';
    $values = '';
    $update_keys ='';
    $update_values = array();

    foreach ($data as $key => $val) {
        $fields .= $key.',';
        $values .= $DB->qstr($val). ',';

        $update_keys .= $key.'=? ,';
        $update_values[]= $val;
    }
    //valid
    $fields = trim($fields,',');
    $values = trim($values,',');
    $update_keys = trim($update_keys, ',');

    if(is_numeric($id)) {   //update svn user
        $sql = "UPDATE svn_wp_users set {$update_keys} WHERE id={$id}";
        $DB->Execute($sql, $update_values);
    }
    else {  //add new user
        $sql ="INSERT INTO svn_wp_users({$fields}) VALUES({$values})";
        $DB->Execute($sql);
    }

    return $DB->Affected_Rows();
}

/**
 * add new or update exist svn user
 * @param array $data
 * @param array $where where data, if not given get $data default
 */
function hw_svn_update_user($data=array(), $where=array()){
    global $DB;
    $insert_cols = '';
    $insert_vals = '';
    $update_keys = '';
    $update_values = array();
    $wheres= '';

    //prepare where clause
    if(is_array($where) && count($where)) {
        foreach($where as $col => $val) {
            $wheres .= $col.'='.$DB->qstr($val).' and ';
        }
    }
    foreach($data as $col => $val) {
        if($val === '') continue;
        $insert_cols .= $col.',';
        $insert_vals .= "'{$val}',";

        $update_keys .= $col.'=? ,';
        $update_values[]= $val;

        //prepare where clause
        if(!is_array($where) || count($where)==0) {
            $wheres .= $col.'='.$DB->qstr($val).' and ';
        }
    }
    //valid
    $insert_cols = trim($insert_cols,',') ;
    $insert_vals = trim($insert_vals,',') ;
    $update_keys = trim($update_keys, ',');
    $wheres = trim($wheres, ' and ');

    //insert if not exist
    $sql ="INSERT INTO `svn_wp_users` ($insert_cols)
SELECT * FROM (SELECT $insert_vals) AS tmp
WHERE NOT EXISTS (
    SELECT * FROM `svn_wp_users` WHERE {$wheres}) LIMIT 1;";
    $DB->Execute($sql);

    //also you want to update
    if(!$DB->Affected_Rows()) {
        $sql ="UPDATE `svn_wp_users` set {$update_keys} WHERE {$wheres}";#echo $sql;_print($update_values);
        $DB->Execute($sql, $update_values);
    }

    return $DB->Affected_Rows();
}
/**
 * delete svn user by id
 * @param $id
 */
function hw_svn_deluser($id) {
    global $DB;
    $DB->Execute('DELETE FROM svn_wp_users WHERE id=?', $id);
    return $DB->Affected_Rows();
}

/**
 * list all svn users from db
 * @param $domain list users by domain, separate by comma
 * @return mixed
 */
function hw_svn_list_users($domain='') {
    global $DB;
    $result = array();
    if($domain) {
        if(is_string($domain)) $domain = preg_split('#[,\s]+#',$domain);
        $domain = array_map( function($t){
            return '"'.$t.'"';
        }, $domain);
        $domain = join(',', $domain);
    }
    $sql = 'SELECT * FROM svn_wp_users';
    if($domain ) $sql .= ' where domain in ('.$domain.') or domain is NULL';

    $rs = $DB->Execute($sql );
    while($row=$rs->FetchRow()){
        $result[$row['id']] = $row;
    }
    return $result;
}

/**
 * get user by
 * @param array $arg
 */
function hw_svn_get_user($arg= array()) {
    global $DB;
    $where='';
    $values = array();
    foreach($arg as $key => $val) {
        $where .= $key.'=? and ';
        $values[] = $val;
    }
    //valid
    $where = trim($where, ' and ');
    $sql ="SELECT * FROM svn_wp_users WHERE {$where} limit 1";
    $rs= $DB->Execute($sql, $values);
    return $rs? $rs->FetchRow(): array();
}

/**
 * add repository to db
 * extend hw_update_dbtable function
 * @param array $data
 * @param array $where
 */
function hw_svn_update_repository ($data = array(), $where= array()) {
    return HW_DB::update_dbtable(HW_DB::svn_repositories, $data, $where);
}

/**
 * delete repository
 * @param array $where
 * @return mixed
 */
function hw_svn_del_repository($where= array()) {
    return HW_DB::del_table_rows(HW_DB::svn_repositories, $where);
}

/**
 * get repositories
 * @param array $arg
 * @param int $limit
 */
function hw_svn_get_repositories($arg = array(), $limit='') {
    global $DB;
    $t_repo = HW_DB::svn_repositories;
    $t_u = HW_DB::svn_wp_users;

    $where='';
    $values = array();
    foreach($arg as $key => $val) {
        $where .= $key.'=? and ';
        $values[] = $val;
    }
    //valid
    $where = trim($where, ' and ');

    $sql = "select t_repo.id as repo_id,t_repo.repository_name,t_u.id as svn_user_id,t_u.svn_user,t_u.svn_fullname, t_u.svn_pass,t_u.svn_email,t_u.domain from {$t_repo} as t_repo left join {$t_u} as t_u on t_repo.svn_user = t_u.svn_user ";
    //where
    if($where) $sql.= ' WHERE '. $where;
    //limit sql
    if($limit && is_numeric($limit)) $sql .= ' limit '.$limit;

    $sql .= " order by t_u.svn_user DESC";

    $rs = $DB->Execute($sql, $values);

    return $rs;
}
#--------------svn_custom.conf--------------------------
/**
 * add repository to svn_custom.conf on local
 * @param $new_repo
 * @param $svn_user
 * @param $cpaneluser
 * @param $force_bind_user2repo
 */
function hw_svn_conf_add_repository($new_repo,$svn_user, $cpaneluser /*, $force_bind_user2repo=false*/) {
    $conf_file = 'svn_custom.conf';
    $commands_path = WHM_CPANEL_SHELL_APP. '/x_ssh/commands';
    $conf =file_get_contents($commands_path.'/'.$conf_file);
    //backup old svn config file
    copy($commands_path.'/'.$conf_file, $commands_path. '/'.$conf_file.'.backup');

    if(!preg_match('#\<location.+?themes\/'.$new_repo.'(\s+)?\>#',$conf)) { //make sure new repo not exists
        //since we use different htpasswd file to storing each user pass
        //so: svn_users/{$svn_user}.svn.htpasswd
        $location = "<location /themes/{$new_repo}>
	DAV svn
	SVNPath /home/{$cpaneluser}/public_svn/themes/{$new_repo}/
	AuthType Basic
	AuthName \"SVN themes/{$new_repo}\"
	AuthUserFile /home/{$cpaneluser}/svn_users/{$svn_user}.svn.htpasswd
	Require valid-user
	ErrorDocument 401 'Sai username hoac mat khau.'
</location>";
        $end='#end location
</IfModule>';

        $arr = explode('#end location', $conf);
        $conf = ($arr[0].PHP_EOL. $location.PHP_EOL.$end);
        $conf = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $conf);    //remove empty lines

        //save changes, wait for 5-10s for apache update with command /scripts/ensure_vhost_includes --all-users
        file_put_contents(WHM_CPANEL_SHELL_APP. '/x_ssh/commands/svn_custom.conf' ,$conf) ;     //$dir.DIRECTORY_SEPARATOR.$conf_file
        /*$res = $cpanel_file->savefile(array(
            'dir'=> $dir,
            'file' => $conf_file,
            'content' => $conf
        ), HW_WHM_ROOT_USER, true, HW_WHM_ROOT_PASS, '2087');
        ajax_output($res);
        */
        return true;
    }
    return false;
    /*elseif($force_bind_user2repo) {
        hw_svn_conf_find_user_repo($new_repo, $svn_user);
    }*/
}

/**
 * find user for repository
 * @param $repo
 * @param string $svn_custom
 * @param string $replace_user
 * @param bool $update_svn_custom
 */
function hw_svn_conf_find_user_repo($repo, $replace_user='', $update_svn_custom=false,$svn_custom='') {
    $conf_name = 'svn_custom.conf';
    $conf_file = WHM_CPANEL_SHELL_APP. '/x_ssh/commands/' .$conf_name;
    $return = array();

    if(empty($svn_custom)) {
        $svn_custom = file_get_contents($conf_file);
    }
    $items=preg_split('#(<location.+?>)|(</location>)#', $svn_custom, NULL,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    #_print($items);echo '<br/>';
    if(count($items)) {
        $found=0;
        $result = array();
        foreach ($items as $line) {
            if(!(trim($line))) continue;
            if(/*strpos($line, '/themes/'.$repo)!==false*/preg_match("#\<location.+\/themes\/{$repo}(\s+)?\>#",$line )) {
                $found=1;
                $result[] = $line;
                continue;
            }
            if($found) {
                $found=0;
                //get svn user passwd
                preg_match('#AuthUserFile\s+(.+)#', $line, $r);
                if(count($r)) {
                    $return['svn_user_file'] = $svn_user_file = trim($r[1]);
                    $return['svn_user'] = $svn_user = str_replace('.svn.htpasswd', '', hw_get_filename(trim($svn_user_file)) );
                    //replace with new user
                    if($replace_user) {
                        $line = str_replace($svn_user.'.svn.htpasswd', "{$replace_user}.svn.htpasswd", $line);
                    }
                }
            }

            $result[] = $line;
        }
        $conf = join(PHP_EOL ,$result);
        $conf = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $conf);    //remove empty lines

        if($update_svn_custom) file_put_contents($conf_file, $conf);
    }
    return $return;
}
/**
 * delete repository define in svn_custom.conf on local
 * @param $repo
 */
function hw_svn_conf_del_repository($repo) {
    $return =  array();
    //modify svn_custom.conf
    $conf_file = WHM_CPANEL_SHELL_APP. '/x_ssh/commands/svn_custom.conf' ;
    $conf = file_get_contents($conf_file);
    $items=preg_split('#(<location.+?>)|(</location>)#', $conf, NULL,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    #_print($items);echo '<br/>';
    if(count($items)) {
        $found=0;$found_end=0;
        $result = array();
        foreach ($items as $line) {
            if(!(trim($line))) continue;
            if(/*strpos($line, '/themes/'.$repo)!==false*/preg_match("#\<location.+\/themes\/{$repo}(\s+)?\>#",$line )) {
                $found=1;
                continue;
            }
            if($found) {
                $found=0;
                //get svn user passwd
                preg_match('#AuthUserFile\s+(.+)#', $line, $r);
                if(count($r)) {
                    $return['svn_user_file'] = $svn_user_file = $r[1];
                }
                $found_end=1;
                continue;
            }
            if($found_end) {
                $found_end=0;
                continue;
            }
            $result[] = $line;
        }
        #_print(join(PHP_EOL, $result));
        $conf = join(PHP_EOL ,$result);
        $conf = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $conf);    //remove empty lines

        if(isset($svn_user_file) && strpos($conf, $svn_user_file) ===false) {   //not found this user assign to any repository, so we will remove this user credential on server
            $return['user4repo_have_not_any_repo'] = true;
            $return['shell_script'] = $shell_script = "rm -rf {$svn_user_file};";
        }

        file_put_contents($conf_file, $conf);
    }
    return $return;
}

/**
 * check & turn on/off freeze mode for the repository
 * @param $authz
 * @param bool $freeze
 */
function hw_svn_freeze_repository ($authz, $freeze=true) {
    $return = array();

    $items=preg_split('#(\#\s+\[hoangweb-freeze-repository\])|(\#\s+\[\/hoangweb-freeze-repository\])#', $authz, NULL,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $found=0;
    $result = array();
    foreach ($items as $line) {
        if(!(trim($line))) continue;
        if(/*strpos($line, '/themes/'.$repo)!==false*/preg_match("#\[hoangweb-freeze-repository\]#",$line )) {
            $found=1;
            $result[] = $line;
            continue;
        }
        if($found) {
            $found=0;
            $ori_line = $line;
            //get svn user passwd
            foreach(array_filter(explode("\r\n", $line)) as $l) {
                //check for repository read-write permission
                if(!$freeze && preg_match('#^(\s+)?\*(\s+)?\=(\s+)?r#', $l, $s)) {	//if only read mean locked
                    $line = str_replace($s[0], '# * = r', $line);	//unlock mode
                }
                if($freeze && !preg_match('#^(\s+)?\*(\s+)?\=(\s+)?r#', $l, $s) && $s) {
                    $line = str_replace($s[0], ' * = r', $line);		//lock mode
                }
            }
            $return['detect_change'] = $ori_line !== $line;
        }

        $result[] = $line;
    }

    $conf = join(PHP_EOL ,$result);
    $conf = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $conf);    //remove empty lines
    $return['authz_content'] = $conf;

    return $return ;
}

/**
 * create repository
 * @param $cp
 */
function svn_create_repo($cp) {
    if($cp->domain!='hoangweb.vn') return;

    $new_repo = _post('repository_name');
    $svn_user = _post('svn_user');  //svn user
    $force_user2repo = _post('force_user2repo');    //force svn user belong to repository
    $autopass= _post('autopass',"0");

    //add to db
    $repo_info = array(
        'repository_name' => $new_repo,
        'svn_user' => $svn_user
    );
    $res = hw_svn_update_repository($repo_info, array('repository_name' => $new_repo));
    ajax_output($res? 'Updated repository in db' : 'Update repository info failt.');

    //update hw wp tool
    $subdomain = _post('subdomain');
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    #$cpanel = authorize_hwm();
    $cpanel_file = $cp->get_instance('fileman');//HW_CPanel_Fileman::loadacct($acc_id, true);

    ### modify svn_custom.conf
    $dir = "/etc/httpd/conf/userdata/std/2/{$cp->cpaneluser}/svn.hoangweb.vn";
    #$conf_file = 'svn_custom.conf';

    //$conf = $cpanel_file->get_filecontent($conf_file,$dir); #do not seem to have access permissions!
    #$conf = $cpanel_file->get_filecontent1($conf_file,$dir, HW_WHM_ROOT_USER,0,HW_WHM_ROOT_PASS,'2087');   #run __svn-create-repo.bat to update svn_custom.conf

    #ajax_output($conf);
    //add repository to svn_custom.conf on local
    if(!hw_svn_conf_add_repository($new_repo, $svn_user, $cp->cpaneluser ) && $force_user2repo) {
        hw_svn_conf_find_user_repo($new_repo, $svn_user, true);
    }


    ### modify svn-create-repo.txt
    $cmd="chmod 755 /home/svn-create-repo.sh
cd /home
dos2unix svn-create-repo.sh
source ./svn-create-repo.sh
#. svn-create-repo.sh

#set permission to pre-commit, post-commit hook It needs to be executable
cd /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo}/hooks
chmod 775 pre-commit
chmod u+x pre-commit
chmod 775 post-commit
chmod u+x post-commit

#convert to unix format
dos2unix pre-commit
dos2unix post-commit
";
    file_put_contents(WHM_CPANEL_SHELL_APP."/x_ssh/commands/svn-create-repo.txt", $cmd);

    ### modify svn-create-repo.sh
    /*$create_repo_linux="alias root1='cd ~'
alias repo='cd public_svn/{$new_repo}'
sudo su
su {$cpaneluser}
cd ~
#. root1

mkdir public_svn
mkdir public_svn/{$new_repo}
cd public_svn/{$new_repo}
svnadmin create svn
#chmod 775 -R svn/*

# make sure to change permission to repository folder
chmod 775 -R /home/{$cpaneluser}/public_svn/{$new_repo}/svn/*

# Add Subversion users (protect repository on the web)
/usr/local/apache/bin/htpasswd -cm ./.svn.htpasswd username

exit

# finally change the permissions on the repo folder so that the svn module can write to the the filesystem
chown -R {$cpaneluser}:nobody /home/{$cpaneluser}/public_svn/{$new_repo}

# update the Apache configuration to use the custom vhost includes:
/scripts/ensure_vhost_includes --all-users

read -n1 -r -p 'Press any key to continue...' key";*/

    //old
    /*$create_repo_linux="echo 'Create repository [{$new_repo}]!'
#sudo su
sudo -u {$cpaneluser} -H sh -c 'cd ~;pwd;mkdir public_svn;echo \"Creating.. {$new_repo} folder.\";mkdir public_svn/{$new_repo};cd public_svn/{$new_repo};echo \"Creating svn..\";svnadmin create svn;cd ~;chmod 775 -R public_svn/{$new_repo}/svn;cd public_svn/{$new_repo};echo \"Enter user pass for your repository ?\";/usr/local/apache/bin/htpasswd -cm ./.svn.htpasswd huy;exit;'
sudo -u root -H sh -c 'cd ~;pwd;echo \"chmod 755 for {$new_repo} folder.\";chmod 775 -R /home/{$cpaneluser}/public_svn/{$new_repo}/svn;chown -R {$cpaneluser}:nobody /home/{$cpaneluser}/public_svn/{$new_repo};echo \"Update vhost\";/scripts/ensure_vhost_includes --all-users;read -n1 -r -p \"Press any key to continue...\" key'
#read -n1 -r -p 'Press any key to continue...' key";*/

    //new
    /*
     * note: use different passwords file to store each user credential. Why? if user htpasswd exists we can use this without to check for exists
     *
     * /usr/local/apache/bin/htpasswd -m ./{$svn_user}.svn.htpasswd $svn_user;
     * (echo \"Sory !Created user pass before.\";/usr/local/apache/bin/htpasswd -mb ./{$svn_user}.svn.htpasswd $svn_user ;) change to:
     * (echo \"Sory !Created user pass before.\";)
     */
    $url_depass="http://{$cp->domain}/ajax.php";
    //run wp tool
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=dbuser_info&acc='.$cp->acc_id;
    $res = curl_get($url);
    if($res) {
        $dbinfo = json_decode($res);

        $mysql_user=$dbinfo->DB_USER;
        $mysql_pass= $dbinfo->DB_PASSWORD;
        $mysql_db= $dbinfo->DB_NAME;
    }
    //valid
    if(!isset($mysql_user) || !isset($mysql_pass) || !isset($mysql_db)) exit('wp tool unable to get database info from '.$cp->domain);

    //remove this before exit command:
    //#cd ~;python hoangapp/hw-userpass1.py --job=\"add_user\" --svn_user=\"{$svn_user}\" --svn_pass=\"\$svn_depass\") ;
    $create_repo_linux="echo 'Create repository [{$new_repo}]!'
#sudo su
sudo -u {$cp->cpaneluser} -H sh -c 'cd ~;pwd;mkdir public_svn;echo \"Creating themes dir\";mkdir public_svn/themes;cd public_svn/themes;[ -d {$new_repo} ] && read -p \"{$new_repo} already exists, override (y/n) ?\" yn && [ \"\$yn\" == \"n\" ] && (echo \"exit !\";exit) || (echo \"Creating..repository themes/{$new_repo} folder.\";svnadmin create --fs-type fsfs {$new_repo};chgrp {$cp->cpaneluser} {$new_repo};chmod o+rx {$new_repo};cd {$new_repo};chgrp -R {$cp->cpaneluser} conf db format hooks locks README.txt dav;chmod g-w conf hooks;chgrp {$cp->cpaneluser} conf/svnserve.conf conf/authz conf/passwd;chmod o+r conf/svnserve.conf;chmod g-w conf/authz conf/passwd;chgrp {$cp->cpaneluser} hooks/*;[ -f /home/{$cp->cpaneluser}/hoangapp/svn_hooks/pre-commit ] && (cp /home/{$cp->cpaneluser}/hoangapp/svn_hooks/pre-commit /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo}/hooks/pre-commit;) || echo \"Khong tim thay hoangapp/../pre-commit\";[ -f /home/{$cp->cpaneluser}/hoangapp/svn_hooks/post-commit ] && cp /home/{$cp->cpaneluser}/hoangapp/svn_hooks/post-commit /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo}/hooks/post-commit || echo \"Khong tim thay hoangapp/../post-commit\";cd ~;chmod 775 -R public_svn/themes/{$new_repo};);cd ~;read -p \"Want to update pass for user [{$svn_user}]? (y/n) ?\" yn && [ \"\$yn\" == \"n\" -a -f svn_users/{$svn_user}.svn.htpasswd ] && (echo \"Sory !Created user pass before.\";) || (echo \"Creating ~svn_users folder to hold users credentials.\";mkdir svn_users;cd svn_users;echo \"Enter user pass [{$svn_user}] for your repository ?\";hw_mysqluser=\"{$mysql_user}\";hw_mysqlpsw=\"{$mysql_pass}\";hw_mysqldatabase=\"{$mysql_db}\";hw_svnpass_query=\"select svn_pass from svn_wp_users where svn_user=\\\"{$svn_user}\\\" limit 1\";svn_enpass=$(mysql -u\${hw_mysqluser} -p\${hw_mysqlpsw} \${hw_mysqldatabase} -e \"\${hw_svnpass_query}\");echo \"My enpass:\$svn_enpass\";svn_depass=$(curl -s -d \"str=\$svn_enpass\" \"{$url_depass}?do=dec_svn_pass\");echo \"My pass: \$svn_depass\"; /usr/local/apache/bin/htpasswd -bc ./{$svn_user}.svn.htpasswd $svn_user \$svn_depass;echo \"saving user pass..to db\");exit;'
sudo -u root -H sh -c 'cd ~;pwd;echo \"chmod 755 for themes/{$new_repo} folder.\";chmod 775 -R /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo};chown -R {$cp->cpaneluser}:nobody /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo};echo \"Update vhost\";/scripts/ensure_vhost_includes --all-users;'
#send mail to user about repository
read -p \"Want to send mail to user [{$svn_user}] about this repository? (y/n) ?\" yn && [ \"\$yn\" == \"y\" ] && (python /home/{$cp->cpaneluser}/hoangapp/hw-mailer_template.py --body=\"sdfsdg\" --tpl=\"create_svn_repository.tpl\" --svn_user=\"{$svn_user}\" --type=\"alert_new_repo\" --repository=\"{$new_repo}\") || (echo \"No send mail about this repository.\");";

    /*$create_repo_linux.="
#update repository hooks
post_hooks='#!/bin/sh\\n\\n# Repository storage path. This is passed in by Subversion\nREPOS=\"\$1\"\n\n# Transaction id of this commit.  This is passed in by Subversion\nREV=\"\$2\"\n\n# Execute the svnlook command to get the author of the commit if any.\nAUTHOR=\"\$(svnlook author -r \$REV \$REPOS)\"\n\n#mailer.py commit \"\$REPOS\" \"\$REV\" /path/to/mailer.conf\n\n# send mail to user when first commit made\npython /home/{$cpaneluser}/hoangapp/hw-mailer.py --only_first_commit=1 --body=\"\$REPOS \$AUTHOR\" --svn_user=\"\$AUTHOR\" --type=\"svn_theme_working_copy\"\n\n# update theme status set to 1 tell hoangweb.vn this repository updated\npython /home/{$cpaneluser}/hoangapp/hw-wp-update-theme-status.py --svn_user=\"\$AUTHOR\" --theme_path=\"\$REPOS\" --theme_status=\"1\"\n\n# add/update post on hoangweb.vn for this theme\npython /home/{$cpaneluser}/hoangapp/hw-wp-add-theme.py --svn_user=\"\$AUTHOR\" --theme_path=\"\$REPOS\"';cd /home/{$cpaneluser}/public_svn/themes/{$new_repo}/hooks;echo -e \$post_hooks > post-commit;chmod 775 post-commit; #read -n1 -r -p 'Press any key to continue...' key;";*/

    $create_repo_linux .= "
##...create post-commit by copying from local to server using terminal
#convert to unix format
#dos2unix post-commit

# It needs to be executable
#chmod u+x post-commit
#chmod 775 post-commit;
    ";

    $create_repo_linux .= "
#modify pre-commit hook
    #cp /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo}/hooks/pre-commit.tmpl /home/{$cp->cpaneluser}/public_svn/themes/{$new_repo}/hooks/pre-commit
##...create pre-commit by copying from local to server using terminal
#convert to unix format
#dos2unix pre-commit

# It needs to be executable
#chmod u+x pre-commit
#chmod 775 pre-commit;
    ";

    file_put_contents(WHM_CPANEL_SHELL_APP."/x_ssh/commands/svn-create-repo.sh",$create_repo_linux);

    //create repository folder & add subversion user
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__svn-create-repo.bat '.$cp->acc_id.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' '.$new_repo.' '.$autopass.'"');
    ajax_output($str);
}
add_action('ajax_task_svn_create_repo', 'svn_create_repo');
/**
 *  delete reponsitory from svn subversion
 * @param $cp
 */
function svn_del_repo($cp) {
    $subdomain = _post('subdomain');
    $repo = _post('repository_name');
    $cmd = _post('cmd');    //command file
    $ssh_batch = _post('ssh_batch');

    $svn_update = WHM_CPANEL_SHELL_APP. '/x_ssh/commands/svn-update.sh';    //update svn subversion  server
    $shell_script = '';

    //del repository from db
    hw_svn_del_repository(array('repository_name' => $repo));

    /*delete wp post for post type 'themes' on $domain*/

    //upload hw wp tool
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=svn_del_wp_theme&acc='.$cp->acc_id;
    $data = array('theme_name' => $repo );
    $res = curl_post($url, $data);
    ajax_output($res);

    /*modify command file (svn-del-repo.txt)*/
    $del_repo= "# Delete a repository from my SVN subversion http://svn.hoangweb.vn/
rm -rf /home/{$cp->cpaneluser}/public_svn/themes/{$repo}
echo 'Deleted /home/{$cp->cpaneluser}/public_svn/themes/{$repo}'
";
    file_put_contents(WHM_CPANEL_SHELL_APP. "/x_ssh/commands/{$cmd}", $del_repo);

    //del repository define in svn_custom.conf on local
    $result = hw_svn_conf_del_repository($repo);
    if(isset($result['shell_script'])) $shell_script = $result['shell_script'];

    //valid svn-update.sh
    if(! file_exists($svn_update)) {
        $svn_reset="echo '[Update SVN server!]'
#sudo su
sudo -u root -H sh -c 'cd ~;pwd;{$shell_script} echo 'Update vhost';/scripts/ensure_vhost_includes --all-users;read -n1 -r -p \"Press any key to continue...\" key'
#read -n1 -r -p 'Press any key to continue...' key
";
        file_put_contents($svn_update, $svn_reset);
    }
    $cp->run_command_as_root($ssh_batch, $cmd);
    /*$str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__ssh-root.bat '.$cp->acc_id.' '.$ssh_batch.' '.$cmd.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' '.$cp->cpaneluser.'"');
    ajax_output($str);*/
}
add_action('ajax_task_svn_del_repo', 'svn_del_repo');

/**
 * dump repository
 * http://svnbook.red-bean.com/en/1.7/svn.ref.svnadmin.c.dump.html
 * @param $cp
 */
function svn_dumprepo($cp) {
    $repo = _post('repository_name');   //repository name
    $cmd= _post('cmd'); //get command file

    //modify command file for removing repository from Subversion (here is: svn-dump-repo.txt)
    $cmd_txt = "# Dump a repository from my SVN subversion http://svn.hoangweb.vn/
'test' > /home/{$cp->cpaneluser}/public_svn/{$repo}/svn-{$repo}.txt
chmod 755 /home/{$cp->cpaneluser}/public_svn/{$repo}/svn-{$repo}.txt
svnadmin dump /home/{$cp->cpaneluser}/public_svn/{$repo} > /home/{$cp->cpaneluser}/public_svn/{$repo}/svn-{$repo}.txt
echo 'Created file /home/{$cp->cpaneluser}/public_svn/{$repo}/svn-{$repo}.txt on server'
";
    file_put_contents(WHM_CPANEL_SHELL_APP. "/x_ssh/commands/{$cmd}", $cmd_txt);

    $cp->run_command_as_root('ssh-root.bat', $cmd);
    /*$str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__ssh-root.bat '.$cp->acc_id.' ssh-root.bat '.$cmd.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' X"');
    ajax_output($str);*/
}
add_action('ajax_task_svn_dumprepo', 'svn_dumprepo');

/**
 * delete extracted folder from repository for web demo
 * @param $cp
 */
function svn_del_extractfiles_repo($cp) {
    $repo_name = _post('repository_name');
    //specific valid filename or directory name, accepts multiple values as a comma-separated list.
    $sourcefiles = "/home/{$cp->cpaneluser}/public_html/demo/{$repo_name}";

    $cpanel_file = $cp->get_instance('fileman');//HW_CPanel_Fileman::loadacct($acc_id);
    $result = $cpanel_file->delfiles($sourcefiles);
    ajax_output($result);
}
add_action('ajax_task_svn_del_extractfiles_repo', 'svn_del_extractfiles_repo');
/**
 *  get demo theme link from hoangweb.vn
 * @param $cp
 */
function svn_get_demo_theme_link($cp) {
    if($cp->domain!='hoangweb.vn') return;
    $theme_name = _req('repository_name');  //repository name as theme name

    $api = hw_valid_url($cp->domain). '/api/hwapi/get_theme_info?theme_name='. urlencode($theme_name);
    $res = curl_get($api);
    $res = json_decode($res) ;
    if(!empty($res->demo_link) ) {
        //format: /preview/5842/cokhi-02?quality=demo
        #$url = hw_valid_url($domain). "/preview/".$res->data->id. "/{$theme_name}?quality=demo";
        $url = $res->demo_link;
        ajax_output($url) ;
    }
    else ajax_output($res) ;
}
add_action('ajax_task_svn_get_demo_theme_link', 'svn_get_demo_theme_link');
/**
 * fix repository hooks copy from server that uploaded from local to server
 * @param $cp
 */
function svn_fix_repository_hooks($cp) {
    $cmd=_post('cmd');
    $repo_list = _post('repositories');     //list repositories folder
    $repo_list = preg_split("#[,\s]+#", trim($repo_list));

    $fixall = _post('fixall', 0);  //fixed all repositories hooks

    $save = WHM_CPANEL_SHELL_APP ."/x_ssh/commands";
    $cmds_txt = "echo 'copy all valid repository hooks into your choose repositories.';"; //modify cmd file,
    $cmds_txt.="cd /home/{$cp->cpaneluser}/public_svn/themes/;";

    if($repo_list && !$fixall) {
        $dirs ='';
        foreach ($repo_list as $repo) {
            $dirs.= "{$repo},";
        }
        $dirs = trim($dirs, ',');
        //valid
        if(count($repo_list) >1) $dirs = "{".$dirs."}";

        $cmds_txt.= "echo {$dirs}/hooks | xargs -r -n1 cp -rv /home/{$cp->cpaneluser}/hoangapp/svn_hooks/*;find {$dirs}/hooks -type f -exec chmod u+x {} +;find {$dirs}/hooks -type f -exec chmod 755 {} +;find {$dirs}/hooks -type f -exec dos2unix {} +;";
    }
    elseif($fixall) {
        $cmds_txt .= "find . -name \"hooks\" | xargs -n 1 cp -i /home/{$cp->cpaneluser}/hoangapp/svn_hooks/*;find $(find . -name \"hooks\" -type d) -type f -exec chmod u+x {} +;find $(find . -maxdepth 1 -type f -name \"hooks\" -type d) -type f -exec chmod 755 {} + ;find $(find . -maxdepth 1 -type f -name \"hooks\" -type d) -type f -exec dos2unix {} +;";
    }
    $cmds_txt.="read -n1 -r -p \"Press any key to continue...\" key";
    file_put_contents($save.'/'.$cmd, $cmds_txt);

    //run batch file
    #$cmd='0';$str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__ssh-root.bat '.$acc_id.' ssh-root.bat '.$cmd.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' '.$cpaneluser.'"');

    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh\commands/&&svn-update-repository-hooks.bat '.$cp->host.' '.$cp->cpaneluser.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' '.$cmd.'"');
    ajax_output($str);
}
add_action('ajax_task_svn_fix_repository_hooks', 'svn_fix_repository_hooks');
/**
 * delete zips file create while create zip from repo by pressing download on the web
 * @param $cp
 */
function svn_del_repo_zips($cp) {
    $cmd=_post('cmd');
    $delall = _post('delall');  //fixed all repositories hooks
    $del_extract_folders = _post('del_extract_folders');  //also want to del_extract_folders
    $repo_list = _post('repositories');     //list repositories folder
    $repo_list = preg_split("#[\s]+#", trim($repo_list));

    $save = WHM_CPANEL_SHELL_APP ."/x_ssh/commands";
    $cmds_txt = "echo 'deleting.. zips files created while try to download repo by admin.';"; //modify cmd file,
    $cmds_txt .= "cd /home/{$cp->cpaneluser}/public_html/demo/;";

    if($repo_list && !$delall) {
        $dirs ='';
        foreach ($repo_list as $repo) {
            $dirs.= "{$repo}\|";
        }
        //valid
        $dirs = trim($dirs, '\|');

        $cmds_txt.= "rm -rf $(find . -maxdepth 1 -type f -regextype sed -regex \".*/\({$dirs}\).*\.tar\.gz\");";
        //delete extract folder from repo
        if($del_extract_folders) {
            $cmds_txt .= "rm -rf $(find . -maxdepth 1 -type d -regextype sed -regex \".*/\({$dirs}\)\");";
        }
    }
    elseif($delall) {
        $cmds_txt .= "rm -rf $(find . -maxdepth 1 -type f | grep \"[a-f0-9\-\_]*\.tar.gz\");";
        //delete extract folders from all repo
        if($del_extract_folders) {
            $cmds_txt .= "rm -rf $(find . -maxdepth 1 -type f | grep \"[a-f0-9\-\_]*\");";
        }
    }
    $cmds_txt.="sleep 10;";
    #$cmds_txt.="read -n1 -r -p \"Press any key to continue...\" key";
    file_put_contents($save.'/'.$cmd, $cmds_txt);

    //run batch file
    $cp->run_command_as_root('ssh-root.bat', $cmd); //
    /*$str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__ssh-root.bat '.$cp->acc_id.' ssh-root.bat '.$cmd.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' '.$cp->cpaneluser.'"');
    ajax_output($str);*/
}
add_action('ajax_task_svn_del_repo_zips', 'svn_del_repo_zips');
/**
 * freeze a repository
 * @param $cp
 */
function svn_freeze_repository($cp) {
    if($cp->domain!='hoangweb.vn') return;
    $repo_name = _req('repository_name');
    $mode = _post('freeze_mode');

    $authz_file = 'authz';  //located: your_repo/conf/authz
    $conf_path = "/home/{$cp->cpaneluser}/public_svn/themes/{$repo_name}/conf" ;

    $cpanel_file = $cp->get_instance('fileman');//HW_CPanel_Fileman::loadacct($acc_id,true);
    $res = $cpanel_file->get_filecontent($authz_file, $conf_path);
    $result = json_decode($res);
    if(isset($result->data->result)) {
        $content = ($result->data->result);
        $result = hw_svn_freeze_repository($content, $mode);
        if($result['detect_change']) {
            //save back to file
            $res = $cpanel_file->savefile(array(
                'dir'           => $conf_path,
                'file'          => $authz_file,
                'content' => $result['authz_content']
            ));
            ajax_output($res);
        }
        else {
            ajax_output("Repository [{$repo_name}] ".($mode? "đã được khóa trước đó." : "đã mở trước đó.") );
        }
    }
    else ajax_output("Can't read file {$conf_path}/{$authz_file}.");
}
add_action('ajax_task_svn_freeze_repository', 'svn_freeze_repository');
/**
 * update all svn users from `svn_wp_users` table
 * @param $cp
 */
function svn_update_users($cp) {
    $cmd= _post('cmd'); //get command file
    $ssh_batch = _post('ssh_batch');

    ### modify svn-update-users-passwd.txt  ==>we don't use
    #$cmd="chmod 755 /home/svn-update-users-passwd.sh;cd /home;source ./svn-update-users-passwd.sh";
    #file_put_contents(WHM_CPANEL_SHELL_APP."/x_ssh/commands/svn-update-users-passwd.txt", $cmd);

    //get users data from local (so if on server not match data with `svn_wp_users` table you shou;d import to server.)
    $htpasswd ='';
    $svn_users = hw_svn_list_users('hoangweb.vn');
    foreach ($svn_users as $u) {
        $svn_user = $u['svn_user'];
        $svn_depass = decrypt($u['svn_pass']);

        $htpasswd .= "/usr/local/apache/bin/htpasswd -bc ./{$svn_user}.svn.htpasswd $svn_user \"{$svn_depass}\";";
    }

    //create .sh file
    $update_svn_users_passwd ="echo 'Update all svn users pass !';
sudo -u {$cp->cpaneluser} -H sh -c 'cd ~;pwd;mkdir svn_users;cd svn_users;{$htpasswd}'
read -n1 -r -p \"Press any key to continue...\" key";
    file_put_contents(WHM_CPANEL_SHELL_APP."/x_ssh/commands/svn-update-users-passwd.txt",$update_svn_users_passwd);

    //update all svn users pass
    $cp->run_command_as_root($ssh_batch, $cmd);

}
add_action('ajax_task_svn_update_users', 'svn_update_users');

/**
 * add svn user to db
 * @param $cp
 */
function add_svn_user($cp) {
    $result=array();
    ### add svn user to db
    $user = array(
        'svn_user' => _post('svn_user'),
        'svn_pass' => encrypt(_post('svn_user_pass')),
        'svn_fullname' => urldecode(_post('svn_fullname')),
        'svn_email' => _post('svn_user_email')
    );
    //add new or update user
    #$res = hw_svn_update_user($user, array('svn_user'=> _post('user')));
    $_user= hw_svn_get_user(array('svn_user' => $user['svn_user']));
    if(count($_user)) { //update exist user
        $res = hw_svn_adduser($user, $_user['id']);
    }
    else $res = hw_svn_adduser($user);   //add new user

    $result[] = $res? "Add svn user [{$user['svn_user']}] successful !":"Add svn user failt !";

    //create wp user, ->No. solution: call ajax to create wp user then invoke this ajax

    ajax_output($result);
}
add_action('ajax_task_add_svn_user', 'add_svn_user');

/**
 * delete svn user
 * @param $cp
 */
function svn_del_user($cp) {
    $domain = 'hoangweb.vn';
    $subdomain = _post('subdomain');
    $user = _post('svn_user');
    $update_svn_user = 1;    //also want to del svn user from `svn_wp_users` table

    //upload hw wp tool
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    $_user = hw_svn_get_user(array(
        'svn_user' => $user,
        //'domain' => $domain
    ));

    if($_user) {
        $res = hw_svn_deluser($_user['id']);    //del svn user from localhost
        if($res) ajax_output('Delete svn user from local.');

        //del on server
        //run this file
        $url = rtrim(_domain($domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=del_user&acc='.$cp->acc_id;
        $data = array('svn_user' => $user ,'update_svn_user' => $update_svn_user);
        $res = curl_post($url, $data);
        ajax_output($res);
    }
}
add_action('ajax_task_svn_del_user', 'svn_del_user');
/**
 * update svn user in db
 * @param $cp
 */
function svn_update_user($cp) {
    $user = _get('svn_user');
    if(is_numeric($user)) {
        $fields = array('svn_fullname','svn_user','svn_pass','svn_email');
        $userdata =array();
        foreach ($fields as $f) {
            if(_post($f)) $userdata[$f] = _post($f);
        }

        if(count($userdata)) hw_svn_adduser($userdata, $user);
    }
}
add_action('ajax_task_svn_update_user', 'svn_update_user');
/**
 * update list svn users
 * @param $cp
 */
function svn_updatelist_users($cp) {
    if($cp->domain!='hoangweb.vn') return;
    $wp_users = $cp->wptool_list_svn_users();   //$acc_id
    foreach($wp_users as $u) {
        $u= (array) $u;
        unset($u['id']);    //remove 'id' key
        hw_svn_update_user((array)$u,
            array('svn_user' => $u['svn_user']));
    }
    echo 'updated svn users list.';
}
add_action('ajax_task_svn_updatelist_users', 'svn_updatelist_users');
/**
 * rebuild svn_custom.conf file
 * @param $cp
 */
function build_svn_custom_conf($cp ) {
    $allow_empty = _post('allow_empty');
    $subdomain = _post('subdomain');
    //upload hw wp tool
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=build_svn_custom.conf&acc='.$cp->acc_id;
    $data = array();
    $conf = curl_post($url, $data);
    #ajax_output($res);
    if((!$conf && $allow_empty) || $conf) {
        file_put_contents(WHM_CPANEL_SHELL_APP. '/x_ssh/commands/svn_custom.conf' ,$conf) ;     //$dir.DIRECTORY_SEPARATOR.$conf_file
        ajax_output('updated '.WHM_CPANEL_SHELL_APP. '/x_ssh/commands/svn_custom.conf');
    }
    else ajax_output('Not allow to update svn_custom.conf, because of update empty content');
}
add_action('ajax_task_build_svn_custom.conf', 'build_svn_custom_conf');
/**
 * list all repositories
 * @param $cp
 */
function svn_list_repositories($cp) {
    $subdomain = _post('subdomain');
    $update_repo_db = _post('update_repo_db');

    //upload hw wp tool
    $res = $cp->upload_wptool(0, $subdomain);
    ajax_output($res);

    //run this file
    $url = rtrim(_domain($cp->domain, $subdomain), '/') . '/'.HW_WP_TOOL_FILE.'?do=svn_list_repositories&acc='.$cp->acc_id;
    $data = array('update_repo_db' => $update_repo_db );
    $res = curl_post($url, $data);
    ajax_output($res) ;
}
add_action('ajax_task_svn_list_repositories', 'svn_list_repositories');
/**
 * upadte repositories on db
 * @param $cp
 */
function svn_update_repositories_db($cp) {
    $repositories_update_rows  = _post('repositories_update_rows');
    if($repositories_update_rows) $repositories_update_rows = unserialize($repositories_update_rows);

    if(is_array($repositories_update_rows))
        foreach ($repositories_update_rows as $row){
            if(count($row) ==2) hw_svn_update_repository($row[0], $row[1]);
        }
}
add_action('ajax_task_svn_update_repositories_db', 'svn_update_repositories_db');

/**
 * svn decode pass from svn-create-repo.sh
 * @param $cp
 */
function dec_svn_pass($cp) {
    $t=explode("\r\n", _req('str'));
    if(count($t) && $t[0] == 'svn_pass') {
        echo decrypt($t[1]);
    }
}
add_action('ajax_task_dec_svn_pass', 'dec_svn_pass');