<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/09/2015
 * Time: 17:27
 */
global $DB;
/**
 * Class HW_DB
 */
class HW_DB  {
    const DB_HOST='';
    const DB_USER='';
    const DB_PASS='';
    const DB_NAME='';

    const svn_wp_users = 'svn_wp_users';
    const svn_repositories = 'svn_repositories';

    /**
     * connect to database
     * @param $host
     * @param $user
     * @param $pass
     * @param $dbname
     */
    public static function connect($host, $user, $pass, $dbname) {
        global $DB;
        $DB = NewADOConnection('mysql');
        $conn=$DB->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        return $conn;
    }
    /**
     * add new or update exist row in table
     * @param string $table table name
     * @param array $data
     * @param array $where where data, if not given get $data default
     */
    public static function update_dbtable($table,$data=array(), $where=array()){
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
        $sql ="INSERT INTO `{$table}` ($insert_cols)
SELECT * FROM (SELECT $insert_vals) AS tmp
WHERE NOT EXISTS (
    SELECT * FROM `{$table}` WHERE {$wheres}) LIMIT 1;";
        $DB->Execute($sql);

        //also you want to update
        if(!$DB->Affected_Rows()) {
            $sql ="UPDATE `{$table}` set {$update_keys} WHERE {$wheres}";#echo $sql;_print($update_values);
            $DB->Execute($sql, $update_values);
        }

        return $DB->Affected_Rows();
    }
    /**
     * delete table rows
     * @param $table
     * @param array $where
     */
    public static function del_table_rows($table, $where = array()) {
        global $DB;
        $wheres= '';
        $where_vals = array();

        //prepare where clause
        if(is_array($where) && count($where)) {
            foreach($where as $col => $val) {
                $wheres .= $col.'=? and ';
                $where_vals[] = $val;
            }
            $wheres = trim($wheres, ' and ');
        }
        if(count($where_vals)) {
            $sql ="DELETE FROM `{$table}` WHERE {$wheres}";echo $sql;
            $DB->Execute($sql, $where_vals);
        }
        return $DB->Affected_Rows();
    }
}