<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/libs/xmlapi.php');
include_once(dirname(__FILE__). '/cpanel.class.php');
/**
 * Class HW_CPanel_Mysql
 */
class HW_CPanel_Mysql extends HW_CPanel {
    public function __construct($ip='', $cpaneluser='', $cpanelpass=''){
        parent::__construct($ip, $cpaneluser, $cpanelpass);
    }

    /**
     * retrieve all databases for cpanel account
     * @param string $cpaneluser
     */
    public function listdbs($cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        $res= $this->xmlapi->api1_query($cpaneluser, 'Mysql','listdbs');
        #$res= $this->xmlapi->api1_query($cpaneluser, 'Mysql','listdbsopt');
        #
        $arr = json_decode($res);
        $list = strip_tags($arr->data->result, '<br>');
        $list = array_filter(explode('|',preg_replace('#<[^>]+>#', '|', $list) ));
        $result = array();
        foreach($list as $db) {
            $dbname=str_replace($cpaneluser. '_', '', trim($db));
            $result[$dbname] = $db;
        }
        return $result;
    }
    /**
     * create database for account
     * @param $databasename
     */
    public function create_database($databasename,$cpaneluser=''){
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        //create database
        $result = $this->xmlapi->api1_query( $cpaneluser, 'Mysql', 'adddb', array($databasename));
        #add_message($result);
        return $result;
    }

    /**
     * del db
     * @param $db
     * @param string $cpaneluser
     */
    public function deldb($db, $cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        if(!empty($db)) return $this->xmlapi->api1_query($cpaneluser, 'Mysql','deldb',array($db));
    }

    /**
     * check mysql database integrity
     * @param $db
     * @param string $cpaneluser
     * @return mixed
     */
    public function checkdb($db, $cpaneluser='') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        if(!empty($db)) return $this->xmlapi->api1_query($cpaneluser, 'Mysql','checkdb',array($db));
    }
    /**
     * add user to database
     * @param $databaseuser
     * @param $databasename
     * @param string $cpaneluser
     */
    public function add_user2db($databaseuser,$databasename, $cpaneluser=''){
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        //add user
        $addusr = $this->xmlapi->api1_query($cpaneluser, "Mysql", "adduserdb", array("".$cpaneluser."_".$databasename."", "".$cpaneluser."_".$databaseuser."", 'all'));
        #add_message($addusr);
        return $addusr;
    }

    /**
     * delete user db
     * @param $dbuser
     * @param $db
     * @param string $cpaneluser
     */
    public function deluserdb($dbuser, $db, $cpaneluser = '') {
        //if you login as root so maybe access more account
        if(!$cpaneluser) $cpaneluser = $this->cpanelUser;

        return $this->xmlapi->api1_query($cpaneluser, 'Mysql','deluserdb', array($db, $dbuser)  );
    }
}