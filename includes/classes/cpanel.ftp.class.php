<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/libs/xmlapi.php');
//include_once(dirname(__FILE__). '/cpanel.class.php');
_load_class('cpanel');

/**
 * Class HW_CPanel_Ftp
 */
class HW_CPanel_Ftp extends HW_CPanel {
    public function __construct($ip='', $cpaneluser='', $cpanelpass=''){
        parent::__construct($ip, $cpaneluser, $cpanelpass);
    }
    /**
     * list ftp from acct
     * @param $username
     * @return mixed
     */
    public function listftp($username) {
        if($this->xmlapi) return $this->xmlapi->listftp($username);
    }

    /**
     * create an ftp
     * @param $ftp
     * @param string $cpaneluser
     */
    public function create_ftp($ftp = array(), $cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        /*$ftp = array(
            'user'    => 'weeones',
            'pass'    => '12345luggage',
            'quota'   => '42',
        );*/
        $user = isset($ftp['user'])? $ftp['user'] : '';
        $pass = isset($ftp['pass'])? $ftp['pass'] : '';
        $quota = isset($ftp['quota'])? $ftp['quota'] : '';
        $homedir = isset($ftp['homedir'])? $ftp['homedir'] : '/';

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Ftp&cpanel_jsonapi_func=addftp&user={$user}&pass={$pass}&quota={$quota}&homedir={$homedir}";

        if($user && $pass ) {
            return $this->cpanelapi($query);
        }

        //does't work
        #if(is_array($ftp) && $this->xmlapi) return $this->xmlapi->api1_query($cpaneluser, 'Ftp', 'add_ftp', $ftp );
    }

    /**
     * del ftp account for acct
     * @param $ftpuser
     * @param string $cpaneluser
     * @return mixed
     */
    public function delftp($ftpuser, $cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $query = "json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Ftp&cpanel_jsonapi_func=delftp&user={$ftpuser}&destroy=1";

        return $this->cpanelapi($query);
    }

    /**
     * set exists ftp pass
     * @param $ftp
     * @param string $cpaneluser
     * @return mixed
     */
    public function setftp_pass($ftp, $cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        //new ftp info
        $ftpuser = isset($ftp['ftp_user'])? $ftp['ftp_user'] : '';
        $ftppass = urlencode(isset($ftp['ftp_pass'])? $ftp['ftp_pass'] : '');

        if($ftpuser && $ftppass) {
            $query = "/json-api/cpanel?cpanel_jsonapi_user={$cpaneluser}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=Ftp&cpanel_jsonapi_func=passwd&user={$ftpuser}&pass={$ftppass}";#echo $query;

            return $this->cpanelapi($query);
        }

    }

    /**
     * list ftp sessions
     * https://documentation.cpanel.net/display/SDK/UAPI+Functions+-+Ftp%3A%3Alist_sessions
     * @param string $cpaneluser
     */
    public function list_ftp_sessions($cpaneluser='') {
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;
        $query = "/execute/Ftp/list_sessions";
        return $this->cpanelapi($query);
    }

    /**
     * kill a ftp session
     * https://documentation.cpanel.net/display/SDK/UAPI+Functions+-+Ftp%3A%3Akill_session
     * @param string $cpaneluser
     */
    public function kill_ftpsession($cpaneluser='') {
    //may be using ssh access & type linux command: kill
    }
}