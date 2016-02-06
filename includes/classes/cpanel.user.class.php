<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/libs/xmlapi.php');
include_once(dirname(__FILE__). '/cpanel.class.php');

/**
 * Class HW_CPanel_User
 */
class HW_CPanel_User extends HW_CPanel {
    /**
     * constructor
     * @param $ip
     * @param $cpaneluser
     * @param $cpanelpass
     */
    public function __construct($ip='', $cpaneluser='', $cpanelpass=''){
        parent::__construct($ip, $cpaneluser, $cpanelpass);
    }
    /*public function HW_CPanel_User($xmlapi, $credentials){
        $this->cpanelUser = $credentials['user'];
        $this->cpanelPass = $credentials['pass'];
        $this->email_domain = $credentials['email'];

        $this->xmlapi = $xmlapi;
    }*/

    /**
     * create user db
     * @param $databaseuser
     * @param $databasepass
     * @param string $cpaneluser
     * @return mixed
     */
    public function create_user($databaseuser, $databasepass,$cpaneluser=''){

        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        if(empty($databaseuser) || empty($databasepass) || empty($cpaneluser)) {
            return;
        }

        //create user
        $usr = $this->xmlapi->api1_query($cpaneluser, "Mysql", "adduser", array($databaseuser, $databasepass));#add_message($usr);
        return $usr;
    }
    /**
     * list all users from db acc
     * @param string $cpaneluser
     */
    public function listUsers($cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $res= $this->xmlapi->api1_query($cpaneluser, 'Mysql','listusers');
        #$res= $this->xmlapi->api1_query($cpaneluser, 'Mysql','listusersopt');

        $arr = json_decode($res);
        $list = strip_tags($arr->data->result, '<br>');
        $list = array_filter(explode('|',preg_replace('#<[^>]+>#', '|', $list) ));
        $result = array();
        foreach($list as $user) {
            $userdb=str_replace($cpaneluser. '_', '', trim($user));
            $result[$userdb] = $user;
        }
        return $result;
    }


    /**
     * del user
     * @param $cpaneluser
     */
    public function deluser($user, $cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        if(! empty($user)) return $this->xmlapi->api1_query($cpaneluser, "Mysql", "deluser", array($user));
    }

    /**
     * update user pass
     * @param $user
     * @param $pass
     * @param string $cpaneluser
     */
    public function setuserPass($user, $pass, $cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        if(! empty($user) && !empty($pass)) {
            return $this->xmlapi->api1_query($cpaneluser, "Mysql", "set_password", array(
                'user'           => $user,
                'password'       => $pass,
            ));
        }
    }
}