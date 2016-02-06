<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 16:25
 */
#==============================================Directory/Utilities====================================================

/**
 * list all files in folder
 * @param $path
 */
function list_files_in_folder($path) {
    $dh  = opendir($path);
    $files = array();
    while (false !== ($filename = readdir($dh))) {
        $files[] = $filename;
    }
    return $files ;
}
/**
 * list folders in folder
 * @param $path
 * @return array
 */
function list_folders($path) {
    #$path = dirname(dirname(__FILE__)). '/uploader/images';
    $results = scandir($path);
    $groups = array();
    foreach ($results as $result) {
        if ($result === '.' or $result === '..') continue;

        if (is_dir($path . '/' . $result)) {
            //code to use if directory
            $groups[$path . '/' . $result] = $result;
        }
    }
    return $groups;
}
/**
 * read file content
 * @param $file
 * @return string
 */
function read_file($file) {
    //way 1
    $handle = fopen($file, "r");
    $txt = fread($handle, filesize($file));
    fclose($handle);
    //way 2
    //file_get_content($file);
    return $txt;
}

/**
 * list files
 * @param $data
 */
function listfiles($data) {
    //get path to list files
    $dir = _post('path');
    if(!$dir && _post('path1') ) $dir = _post('path1');

    //type
    $types = $type = _post('type');
    if($type == 'zip') $types = 'file';

    /*
        $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Fileman&cpanel_jsonapi_func=listfiles&checkleaf=1&dir={$dir}&filelist=0&showdotfiles=0&types={$types}";

        $cpanel = HW_CPanel::loadacct_instance($acc_id, false);
        $result = $cpanel->cpanelapi($query);
        //ajax_output($result) ;
        */
    //authorize
    $cpanel_file = HW_CPanel_Fileman::loadacct($data->acc_id);
    $result = $cpanel_file->listfiles(array(
        'type'=> _post('type'),
        'dir' => _post('path'),
        'dir1' => _post('path1')
        #'dir' => '/'
    ));
    $result = json_decode($result);

    if(isset($result->cpanelresult->data) && count($result->cpanelresult->data)) {
        echo '<table border="1px" cellpadding="3" cellspacing="1" class="hover-table">';
        echo '<tr>
            <td><strong>File/folder</strong></td>
            <td><strong>fullpath</strong></td>
            <td><strong>chmod</strong></td>
            <td><strong>Size</strong></td>
            <td><strong>type</strong></td>
            <td></td>
        </tr>';
        foreach ($result->cpanelresult->data as $file) {
            if($file->type == 'file' && $type =='zip' && !preg_match('#(\.tar\.gz|\.zip)$#', $file->file)) continue;
            //unzip link
            $unzip_opt ='';
            if($file->type == 'file' && preg_match('#(\.tar\.gz|\.zip)$#', $file->file)) {
                $unzip_opt = '<a class="tooltip-top" title="Extract this zip file" href="javascript:void(0)" data-cpid="'.$data->acc_id.'" onclick="hw_extractfile(this, \''.$file->file.'\', \''.$file->path.'\')">extract</a>';
            }
            //del link
            $del_link = '<a class="tooltip-top" title="Delete file on server" href="javascript:void(0)" data-cpid="'.$data->acc_id.'" onclick="hw_delfile(this, \''.$file->file.'\', \''.$file->path.'\')">Delete</a>';

            $move_link = '<a class="tooltip-top" title="Move this file" id="" href="javascript:void(0)" data-cpid="'.$data->acc_id.'" onclick="hw_moveobj(this,\''.$file->fullpath.'\' )">Move file/folder</a>';
            if($file->type == 'dir') {
                //$move_dirfiles_link = '<a id="" href="javascript:void(0)" data-cpid="'.$acc_id.'" onclick="hw_moveobj(this,\''.$file->fullpath.'\' )">Move file/folder</a>';
            }
            //$cpanel_pathfile = str_replace('/home/'.$cpaneluser.'/','',$file->fullpath);
            $download_link = '<a class="tooltip-top" title="Download this file" id="" href="javascript:void(0)" data-cpid="'.$data->acc_id.'" onclick="hw_downloadfile(this,\''.$file->fullpath.'\' )">Download</a>';
            $transer2other_server= '<a class="tooltip-top" title="Transfer this backup to other server" id="" href="javascript:void(0)" data-cpid="'.$data->acc_id.'" onclick="hw_transfer_backup_4other_acct(this ,\''.$data->acc_id.'\')" data-file="'.$file->fullpath.'">Transfer to other server</a>';

            echo '<tr>';
            echo "<td>{$file->file}</td>";
            echo "<td>{$file->fullpath}</td>";
            echo "<td>{$file->nicemode}</td>";
            echo "<td>{$file->humansize}</td>";
            echo "<td>{$file->type}</td>";
            echo "<td>{$unzip_opt} | {$del_link} | {$move_link}|{$download_link}<br/>{$transer2other_server}</td>";
            echo '</tr>';
        }
        echo '</table>';
    }
}
add_action('ajax_task_listfiles', 'listfiles');
/**
 * extract file
 * @param $data
 */
function extractfile($data) {
    //authorize
    #$cpanel_file = HW_CPanel_Fileman::loadacct($acc_id);
    $cpanel_file = $data->get_instance('fileman1');
    $res = $cpanel_file->extractfile(array(_post('path'),_post('file')));
    ajax_output($res);
}
add_action('ajax_task_extractfile', 'extractfile');
/**
 * delete file
 * @param $data
 */
function delfile($data) {
    $cpanel_file = $data->get_instance('fileman1');
    $res = $cpanel_file->delfile(array(_post('path'),_post('file')));
    ajax_output($res);
}
add_action('ajax_task_delfile', 'delfile');
/**
 * create directory
 * @param $data
 */
function createdir($data) {
    $cpanel_file = $data->get_instance('fileman_no_auth');
    $result = $cpanel_file->createdir(array(_post('path'), _post('name')));
    ajax_output($result);
}
add_action('ajax_task_createdir', 'createdir');

/**
 * empty .trash folder for cpaneluser from /home directory
 * @param $data
 */
function empty_trash($data) {
    $cpanel_file = $data->get_instance('fileman_no_auth');//HW_CPanel_Fileman::loadacct($data->acc_id);
    $result = $cpanel_file->empty_trash();
    ajax_output($result);
}
add_action('ajax_task_empty_trash', 'empty_trash');
/**
 * perform one file or more files (directory)
 * @param $data
 */
function performfiles($data) {
    $source = _post('file');
    $dir = _post('movedir');
    $opt = _post('op');

    $cpanel_file = $data->get_instance('fileman_no_auth');//HW_CPanel_Fileman::loadacct($data->acc_id);
    $result = $cpanel_file->performfile($source, $dir, $opt);
    ajax_output($result);
}
add_action('ajax_task_performfiles', 'performfiles');
/**
 * download file
 * @param $data
 */
function downloadfile($data) {
    $file = _post('file');
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__downloadfile.bat '.$data->acc_id.' '.$file.'"');
    ajax_output($str);
}
add_action('ajax_task_downloadfile', 'downloadfile');
/**
 * transfer file to other server
 * @param $data
 */
function transfer_file($data) {
    $file = _post('file');
    $target_acc= _post('target_acc');
    $rename_file =_post('rename_userfile');

    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__move-file.bat '.$data->acc_id.' '.$file.' '.$target_acc.' '.$rename_file.'"');
    ajax_output($str);
}
add_action('ajax_task_transfer_file', 'transfer_file');
/**
 * chmod dir(755) & all files in folder (644)
 * @param $data
 */
function _chmod($data) {
    $dir=_post('dir');
    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__chmod.bat '.$data->acc_id.' '.$dir.'"');
    ajax_output($str);
}
add_action('ajax_task_chmod', '_chmod');
/**
 * open file uploader
 * @param $cp
 */
function open_fileuploader($cp) {
    $file = _post('filepath');
    $is_root =_post('root_access');
    $savepath= _post('upload_path');

    #$root_user=HW_WHM_ROOT_USER;
    #$root_pass= HW_WHM_ROOT_PASS;

    $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_uploader/&&__fileuploader.bat '.$cp->acc_id.' '.$file.' '.$is_root.' '.$savepath.'"');
}
add_action('ajax_task_open_fileuploader', 'open_fileuploader');