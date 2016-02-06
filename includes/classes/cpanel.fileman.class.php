<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/libs/xmlapi.php');
include_once(dirname(__FILE__). '/cpanel.class.php');

/**
 * Class HW_CPanel_Fileman
 */
class HW_CPanel_Fileman extends HW_CPanel {
    public function __construct($ip='', $cpaneluser='', $cpanelpass=''){
        parent::__construct($ip, $cpaneluser, $cpanelpass);
    }

    /**
     * list files
     * @param array $opts
     * @param string $cpaneluser
     */
    public function listfiles($opts, $cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        $type = '';
        $dir1 = $dir = '';

        extract($opts);

        if(!$dir && $dir1 ) $dir = $dir1;

        //type
        $types = $type;
        if($type == 'zip') $types = 'file';

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Fileman&cpanel_jsonapi_func=listfiles&checkleaf=1&dir={$dir}&filelist=0&showdotfiles=0&types={$types}";

        $result = $this->cpanelapi($query);
        return $result;
    }

    /**
     * extract file
     * @param array $path
     * @param $cpaneluser
     */
    public function extractfile($path, $cpaneluser ='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        //set permission to current folder
        $this->performfile($path[0], '', 'chmod', $cpaneluser, 'metadata=0755');

        $res = $this->xmlapi->api1_query($cpaneluser, 'Fileman', 'extractfile', $path );
        $result = json_decode($res);
        return $result;

        /*if(isset($result->error) && $result->error) {
            return $result->error;
        }
        if(isset($result->event->result) && $result->event->result=='1') {
            return 'Folder Extracted success !';
        }
        else return 'Error.';*/
    }

    /**
     * delete file
     * https://documentation.cpanel.net/display/SDK/cPanel+API+1+Functions+-+Fileman::delfile
     * @depricated use ->delfiles instead
     * @param $path
     * @param string $cpaneluser
     * @return mixed
     */
    public function delfile($path, $cpaneluser ='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $res = $this->xmlapi->api1_query($cpaneluser, 'Fileman', 'delfile', $path );
        $result = json_decode($res);var_dump($result);

        if(isset($result->error) && $result->error) {
            return $result->error;
        }
        if(isset($result->event->result) && $result->event->result=='1') {
            return 'File/folder removed success !';
        }
        return 'Error.';
    }

    /**
     * delete one or more files
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+Fileman%3A%3Afileop
     * @param string $cpaneluser
     */
    public function delfiles($sourcefiles, $cpaneluser= '') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $result = $this->performfile($sourcefiles, '', 'unlink', $cpaneluser);

        return $result;
    }

    /**
     * create dir
     * @param $path
     * @param string $cpaneluser
     */
    public function createdir($path, $cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Fileman&cpanel_jsonapi_func=mkdir&path={$path[0]}&name={$path[1]}";

        $result = $this->cpanelapi($query);
        return $result;
    }

    /**
     * perform one file or more files (directory)
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+Fileman::fileop
     * @param $sourcefiles
     * @param $destfiles
     * @param string $op
     * @param string $cpaneluser
     * @param string $params
     */
    public function performfile($sourcefiles, $destfiles, $op='move', $cpaneluser='', $params='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Fileman&cpanel_jsonapi_func=fileop&op={$op}&sourcefiles={$sourcefiles}&destfiles={$destfiles}&doubledecode=1";
        if(is_string($params)) $query .= '&'.$params;

        $result = $this->cpanelapi($query);
        return $result;
    }

    /**
     * remove all files in .trash folder
     * @param string $cpaneluser
     * @return mixed
     */
    public function empty_trash($cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        return $this->performfile("/home/{$cpaneluser}/.trash", '', 'unlink', $cpaneluser);
    }

    /**
     * create backup
     * @param string $saveto
     * @param string $email_notify
     */
    public function create_backup($saveto='' , $email_notify='') {
        $host = $this->host;
        $cpaneluser = $this->cpanelUser;
        $cpanelpass = $this->cpanelPass;
        if(!$email_notify) $email_notify = $this->email_domain;
        if(!$saveto) {
            $saveto = 'public_html/hw_backups';
        }

        //parse path
        $saveto = trim(trim($saveto),'\/');
        $x= preg_split('#[\\|\/]#', $saveto);
        $name = array_pop($x);
        $path = join('/', $x);

        //create folder
        $this->xmlapi->api1_query($cpaneluser, 'Fileman', 'fmmkdir', array ($path, $name) );

        $_args = array(
            'passiveftp',
            $host,
            $cpaneluser,
            $cpanelpass,
            $email_notify,
            21,
            $saveto  # save to , i don't known?
        );

        return $this->xmlapi->api1_query($cpaneluser,'Fileman','fullbackup',$_args);
    }

    /**
     * restore backup cpanel
     * https://documentation.cpanel.net/display/SDK/WHM+API+1+Functions+-+restoreaccount
     * @param $cpaneluser
     * @return mixed
     */
    public function restore_backup($host='', $port='2087',$cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        if(!$host) $host = $this->host;

        $query = "/json-api/restoreaccount?api.version=1&user={$cpaneluser}&type=monthly&all=0&mail=0&mysql=1&sub=0";

        //root access
        $userroot= 'root';
        $code = read_file('db/snippet.hw');

        #$result = $this->cpanelapi($query);
        $res = $this->callapi_accesshash($query ,null, $port, $userroot, $code, $host);
        return $res;
    }

    /**
     * get dir
     * @param $path
     * @return mixed
     */
    public function getpath($path) {
        $query = "/json-api/cpanel?cpanel_jsonapi_user={$this->cpanelUser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Fileman&cpanel_jsonapi_func=getpath&dir={$path}";

        $result = $this->cpanelapi($query);
        return $result;
    }

    /**
     * upload files to server
     * @param $file
     * @param $target
     * @param string $cpaneluser
     */
    public function uploadfiles($file,$target,$cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        #$file = urlencode('wp.png');
        $target = rtrim(rtrim($target,'/'),'\\');
        #$result = $this->xmlapi->api1_query($cpaneluser,'Fileman','uploadfiles', array ($target, $file/*, 'file2-myotherfile.txt'*/));
        #$result = $this->xmlapi->api1_query($cpaneluser,'Fileman','upload_files', array ($target, $file/*, 'file2-myotherfile.txt'*/));
        #$query = "/execute/Fileman/upload_files?dir={$target}&file={$file}";
        $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Fileman&cpanel_jsonapi_func=uploadfiles&dir={$target}";  //&file-={$file}";

        #$cfile = new CURLFile('C:\tmp\hqa\img\web\images/bg-menu.png','image/png', 'filename.png');
        $cfile = new CURLFile($file['fullpath'],$file['type'], $file['name']);

// Assign POST data
        $data = array('file-' => $cfile);

        $result = $this->cpanelapi($query, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));
        return $result;
    }
    public function downloadfiles() {

    }

    /**
     * get file content
     * https://documentation.cpanel.net/display/SDK/UAPI+Functions+-+Fileman%3A%3Aget_file_content
     * @param $file file name with extension
     * @param $path path to file
     * @param string $cpaneluser
     */
    public function get_filecontent($file,$path, $cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $res = $this->xmlapi->api1_query($cpaneluser, 'Fileman', 'getfile', array ($path,$file) );
        return $res;
    }

    /**
     * get file content
     * https://documentation.cpanel.net/display/SDK/UAPI+Functions+-+Fileman%3A%3Aget_file_content
     * @param $file
     * @param $path
     * @param string $cpaneluser
     * @param bool $root
     * @param string $pass
     * @param string $port
     * @param string $host
     */
    public function get_filecontent1($file,$path, $cpaneluser='',$root=false, $pass='', $port = '2083', $host='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        if(empty($host)) $host = $this->host;

        $query="/execute/Fileman/get_file_content?dir={$path}&file={$file}&charset=utf-8&_DETECT_&_LOCALE_";
        if($root==false) $res = $this->cpanelapi($query);
        else {
            $res = $this->callapi($query, null,$port,$cpaneluser,$pass, $host);
        }
        return $res;
    }

    /**
     * save file on server
     * @param array $filedata
     * @param string $cpaneluser
     * @return mixed
     */
    public function savefile($filedata, $cpaneluser='',$root=false, $pass='', $port = '2083', $host='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        if(empty($host)) $host = $this->host;
        /*
        $filedata = array(
            'dir'           => 'public_html',
            'file'          => 'example.html',
            'from_char'     => 'UTF-8',
            'to_char'       => 'ASCII',
            'content'       => '"hi"',
            'fallback'      => '0',
        );*/
        $dir = isset($filedata['dir'])? $filedata['dir'] : '';
        $file = isset($filedata['file'])? $filedata['file'] : '';
        $content = isset($filedata['content'])? $filedata['content'] : '';

        /*$arg = array(
            'from_char'     => 'UTF-8',
            'to_char'       => 'UTF-8',
            'fallback'      => '0',
        );
        $arg = array_merge($arg, $filedata);
        $res = $this->xmlapi->api1_query($cpaneluser, 'Fileman', 'get_file_content',$arg );
        */

        $query = "/execute/Fileman/save_file_content?&dir={$dir}&file={$file}&from_char=utf-8&to_char=utf-8&fallback=0";
        $custom = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array('content' => $content)
        );
        if($root==false) $res = $this->cpanelapi($query, $custom);
        else {
            $res = $this->callapi($query, $custom,$port,$cpaneluser,$pass, $host);
        }
        return $res;
    }
}