<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/libs/xmlapi.php');
/**
 * Class HW_CPanel
 */
class HW_CPanel{
    /**
     * accesshash
     * @var
     */
    static $hashroot;
    /**
     * security tokens for cpanel
     * @var array
     */
    private static $tokens = array();

    /**
     * xmlapi object
     * @var
     */
    public $xmlapi;

    /**
     * credentials
     */
    public $host;
    public $domain;
    public $cpanelUser;
    public $cpanelPass;
    public $email_domain;
    public $port = '2083';

    /**
     * constructor
     * @param string $ip
     * @param string $cpaneluser
     * @param string $cpanelpass
     * @param int $auth
     */
    function __construct($ip='', $cpaneluser='', $cpanelpass='',$auth=1) {
        //save credentials
        $this->host = $ip;
        $this->cpanelUser = $cpaneluser;
        $this->cpanelPass = $cpanelpass;

        if($auth) $this->get_authorize();
    }

    /**
     * load cpanel account info from db
     * @param $acc_id
     */
    public static function loadacct_instance($acc_id, $auth=1) {
        $acc = get_cpanel_acc( $acc_id );
        $host = !empty($acc['cpanel_host'])? $acc['cpanel_host'] : '';
        $domain = !empty($acc['cpanel_domain'])? $acc['cpanel_domain'] : '';
        $user = !empty($acc['cpanel_user'])? $acc['cpanel_user'] : '';
        $pass = !empty($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) : '';
        $email = !empty($acc['cpanel_email'])? $acc['cpanel_email'] : '';
        $token = !empty($acc['token'])? $acc['token'] : '';

        if($host && $user && $pass) {
            $inst = new self($host, $user, $pass, $auth);
            $inst->email_domain = $email;
            $inst->domain = $domain;
            self::$tokens[$user] = $token;    //store security token for this acct
            return $inst;
        }
        else exit('Miss credential.');

    }
    /**
     * init
     * @param $xmlapi
     */
    public static function init(HW_CPanel $xmlapi){
        $called_class = get_called_class();
        $inst = new $called_class;
        //backup data move to new instance of parent class
        $inst->host = $xmlapi->host;
        $inst->domain = $xmlapi->domain;
        $inst->cpanelUser = $xmlapi->cpanelUser;
        $inst->cpanelPass = $xmlapi->cpanelPass;
        $inst->email_domain = $xmlapi->email_domain;

        $inst->xmlapi = $xmlapi->xmlapi;
        return $inst;
    }

    /**
     * @param $acc_id
     * @param $auth inherit from static::loadacct_instance
     */
    public static function loadacct($acc_id, $auth=0) {
        //authorize
        $cpanel = self::loadacct_instance($acc_id,$auth);
        if($cpanel instanceof HW_CPanel) return self::init($cpanel);
        else {
            throw new Exception("Miss cpanel credential, or please create token for this cpanel account.");
        }
    }
    /**
     * authorize cpanel
     * @param $testapi
     */
    public function get_authorize($testapi = false) {
        if(!empty($this->host) && !empty($this->cpanelUser) && !empty($this->cpanelPass)) {
            try{
                $xmlapi = new xmlapi($this->host);
                #$xmlapi->hash_auth("root",$root_hash);
                $xmlapi->password_auth($this->cpanelUser,$this->cpanelPass);
                $xmlapi->set_port($this->port);
                $xmlapi->set_output("json");

                $xmlapi->set_debug(1);
                $this->xmlapi = $xmlapi;
                if($testapi) return $xmlapi->listftp($this->cpanelUser);
            }
            catch(Exception $e) {
                print_r($e);
            }

        }

    }

    /**
     * root access
     * @param $domain
     * @param $root_hash
     * @return xmlapi
     */
    public static function authorize_root($domain, $root_hash) {
        self::$hashroot = $root_hash;   //save hash

        try{
            $xmlapi = new xmlapi($domain);
            $xmlapi->hash_auth(HW_WHM_ROOT_USER,$root_hash);
            $xmlapi->set_output("json");

            $xmlapi->set_debug(1);

            $inst= new self();
            $inst->host = $domain;
            #$inst->cpanelUser = 'root';
            #$inst->cpanelPass = $root_hash;
            $inst->xmlapi = $xmlapi;

            return $inst;
        }
        catch(Exception $e) {
            print_r($e);
        }
    }


    /**
     * call api url
     * @param $api_url
     * @param $custom_curl
     * @param $port 2087 for whm, 2083 for cpanel,..
     * @param $user
     * @param $pass
     * @param $host
     * @return mixed
     */
    public function cpanelapi( $api_url, $custom_curl = array(),$port = '2083', $user='', $pass='', $host='') {

        if(empty($user)) $user = $this->cpanelUser;
        if(empty($pass)) $pass = $this->cpanelPass;
        if(empty($host)) $host = $this->host;

        $security_token = isset(self::$tokens[$user])? self::$tokens[$user] : '';
        //valid
        if(!$security_token) return "Miss token for this cpanel.";

        $query = "https://{$host}:{$port}/".ltrim($security_token,'/').'/'. ltrim($api_url, '/');

        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($user.":".$pass) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
        //custom curl options
        if(is_array($custom_curl) && count($custom_curl)){
            curl_setopt_array($curl, $custom_curl);
        }

        if($user && $pass) {
            $result = curl_exec($curl);
            if ($result == false) {
                error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
                // log error if curl exec fails
            }
            curl_close($curl);
            return $result;
        }

    }

    /**
     * @param $api_url
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $host
     * @return mixed
     */
    public function callapi( $api_url,$custom_curl=null, $port = '2083', $user='', $pass='', $host='') {
        if(empty($user)) $user = $this->cpanelUser;
        if(empty($pass)) $pass = $this->cpanelPass;
        if(empty($host)) $host = $this->host;

        $query = "https://{$host}:{$port}/". ltrim($api_url, '/');var_dump($query);

        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($user.":".$pass) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
        //custom curl options
        if(is_array($custom_curl) && count($custom_curl)){
            curl_setopt_array($curl, $custom_curl);
        }
        if($user && $pass) {
            $result = curl_exec($curl);
            if ($result == false) {
                error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
                // log error if curl exec fails
            }
            curl_close($curl);
            return $result;
        }

    }

    /**
     * call api with root access
     * @param $api_url
     * @param string $port
     * @param string $user
     * @param string $hash
     * @param string $host
     * @return mixed
     */
    public function callapi_accesshash($api_url, $custom_curl=array(),$port='2087',$user='root', $hash='', $host='') {
        if(empty($host)) $host = $this->host;

        # The contents of /root/.accesshash
        $query = "https://{$host}:{$port}/". ltrim($api_url, '/');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

        $header[0] = "Authorization: WHM $user:" . preg_replace("'(\r|\n)'","",$hash);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
        curl_setopt($curl, CURLOPT_URL, $query);
        //custom curl options
        if(is_array($custom_curl) && count($custom_curl)){
            curl_setopt_array($curl, $custom_curl);
        }
        if($user && $hash) {
            $result = curl_exec($curl);
            if ($result == false) {
                error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
            }
            curl_close($curl);
            return $result;
        }

    }

    /**
     * create user session or get security token, require root access
     * @param $cpuser cpanel user for getting session
     */
    public function create_cpuser_session($cpid) {
        global $DB;
        $userroot= 'root';
        $code = read_file('db/snippet.hw');

        if(is_numeric($cpid)) {
            $acc = get_cpanel_acc($cpid);
            $host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
            $user = isset($acc['cpanel_user'])? $acc['cpanel_user']:"";
            #$pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :'';
        }

        if($user && $host) {
            $url = "/json-api/create_user_session?api.version=1&user={$user}&service=cpaneld&locale=en";
            $result = $this->callapi_accesshash($url, null,'2087', $userroot, $code, $host );
            #$result = $this->callapi($url, null,'2087', $user, '', $host );
            if(!empty($result) ) {
                $result =json_decode($result);
                if(isset($result->data->cp_security_token) ) {
                    $token = $result->data->cp_security_token;  //cpanel token
                    $expires = $result->data->expires;  //token expires

                    //update cpanel info
                    update_cpacct_token($cpid, array(
                        'token' => $token,
                        'expires' => $expires
                    ));

                }
            }
        }

    }
    /**
     * create cpanel account
     * https://documentation.cpanel.net/display/SDK/WHM+API+0+Functions+-+createacct
     * @param $acct
     */
    public function createacct($acct = array()) {
        $default_acct = array(
            #'username' => "someuser",
            #'password' => "pass123",
            #'domain' => "thisdomain.com",
            'pkgname' => 'KhachHang',
            #'savepkg' => '1',
            'featurelist' => 'default',
            'quota' => '750',
            'cgi' => '1',

            'bwlimit' => '15000',   //The account's maximum bandwidth use.
            'maxpop' => '5',    //The account's maximum number of email accounts.
            'maxlst' => '5',    //The account's maximum number of mailing lists.
            'maxsql' => '2',    //The account's maximum number of each available type of SQL database
            'maxsub' => '5',    //The account's maximum number of subdomains.
            'maxpark' => '2',   //The account's maximum number of parked domains.
            'maxaddon' => '2',  //The account's maximum number of addon domains.

            'frontpage' => '1',
            'hasshell' => '1',  //ssh access
            #'contactemail' => 'hoangsoft90@gmail.com',
            'maxftp' => '5',

            'cpmod' => 'x3', //The account's cPanel theme.
            'language' => 'vi'
        );
        $acct = array_merge($default_acct, $acct);
        $res= json_decode($this->xmlapi->createacct($acct));
        return $res;

    }

    /**
     * modify account
     * https://documentation.cpanel.net/display/SDK/WHM+API+1+Functions+-+modifyacct
     *
     * @param string $account
     * @param array $args reference in createacct method
     */
    public function modifyacct($args, $account='',$host='', $port='2087') {
        if(empty($account)) $account = $this->cpanelUser;
        if(empty($host)) $host = $this->host;
        #$res = $this->xmlapi->modifyacct($account, $args ); //need whm root account
        //root access
        $userroot= 'root';
        $code = read_file('db/snippet.hw');

        //valid
        if(!is_array($args) ) $args = array() ;
        //note distingish case intensive
        $acc_opts = 'BWLIMIT,HASSHELL,contactemail,DNS,HASCGI,HASSPF,LOCALE,MAXADDON,MAXFTP,MAXLST,MAXPARK,MAXPOP,MAXSQL,MAXSUB,QUOTA,frontpage';

        foreach($args as $opt=>$val ) {
            if(!in_array($opt, explode(',', $acc_opts) )) {
                unset($args[$opt]);
            }
        }

        $query="/json-api/modifyacct?api.version=1&user={$account}&". http_build_query($args);
        /*$data = array(
            CURLOPT_POST =>1,
            CURLOPT_POSTFIELDS => $args
        );*/
        #$res = $this->cpanelapi($query ,$data);
        $res = $this->callapi_accesshash($query ,null, $port, $userroot, $code, $host);
        return $res;
    }
    /**
     * list accts
     * @return mixed
     */
    public function listAccts() {
        return $this->xmlapi->listaccts();
    }

    /**
     * delete account
     * @param $username
     */
    public function delAcct($username) {
        return $this->xmlapi->removeacct($username);
    }

    /**
     * set user password
     * @param $user
     * @param $pass
     * @param $host
     * @param $port
     */
    public function setAcctPass($user, $pass, $host='', $port='2087') {
        if(empty($host)) $host = $this->host;
        //root access
        $userroot= 'root';
        $code = read_file('db/snippet.hw');
        $pass = urlencode($pass);

        $query = "/json-api/passwd?user={$user}&pass={$pass}";
        $result = $this->callapi_accesshash($query,null, $port, $userroot, $code, $host );
        #$result = $this->callapi($query, null,'2087', 'root', 'u2Z6qKJAKgQgGdc-', $host );
        return $result;
    }
    /**
     * create cron job
     * @param $args params
     */
    public function create_cron($args = array()) {
        /*$command = "/usr/bin/php cron.php";
        $day = "1";
        $hour = "1";
        $minute = "1";
        $month = "1";
        $weekday = "1";*/

        $account = $this->cpanelUser;   //cpanel acct
        /*
        * @api2_query(account, module, function, params)
        */
        return $this->xmlapi->api2_query($account, "Cron", "add_line", $args);
    }

    /**
     * list all cron jobs
     * @return mixed
     */
    public function listcrons() {
        $account = $this->cpanelUser;
        return $this->xmlapi->api2_query($account, "Cron", "listcron");
    }

    /**
     * delete cronjob
     * @param $id
     * @return mixed
     */
    public function delcron($id) {
        $account = $this->cpanelUser;
        $res = $this->xmlapi->api2_query($account, "Cron", "remove_line", array(
            "line" => $id
        ));
        return $res;
    }

    /**
     * generate  ssh key for account
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SSH::genkey
     * @param string $account
     * @param $keyname
     * @param $keypass
     */
    public function generate_sshkey($keyname, $keypass,$account='') {
        if(!$account) $account = $this->cpanelUser;

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SSH&cpanel_jsonapi_func=genkey&bits=1024&name={$keyname}&pass={$keypass}&type=rsa&bits=2048";

        $result = $this->cpanelapi($query );
        return $result ;
    }

    /**
     * list aall ssh keys from account
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SSH%3A%3Alistkeys
     * @param $account
     */
    public function list_sshkeys($account='') {
        if(!$account) $account = $this->cpanelUser;
        $query = "/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SSH&cpanel_jsonapi_func=listkeys";

        $result = $this->cpanelapi($query);
        return $result;
    }

    /**
     * delete ssh key
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SSH%3A%3Adelkey
     * @param $keyname key name
     * @param $pub 0|1 for private/public key
     * @param string $account
     */
    public function del_sshkey($keyname, $pub=0,$account='') {
        if(!$account) $account = $this->cpanelUser;
        $query ="/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SSH&cpanel_jsonapi_func=delkey&name={$keyname}&pub={$pub}";

        $result = $this->cpanelapi($query);
        return $result;
    }
    /**
     * authorize ssh key
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SSH%3A%3Aauthkey
     * @param string $account
     * @param string $key
     * @param string $action
     */
    public function authorizes_sshkey($key, $action='authorize', $account='') {
        if(!$account) $account = $this->cpanelUser;

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SSH&cpanel_jsonapi_func=authkey&key={$key}&action={$action}";

        $result = $this->cpanelapi($query );
        return $result ;
    }

    /**
     * convert ssh key to ppk file
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SSH%3A%3Aconverttoppk
     * @param $key
     * @param $pass
     * @param string $account
     */
    public function sshkey_converttoppk($key, $pass,$account='') {
        if(!$account) $account = $this->cpanelUser;

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SSH&cpanel_jsonapi_func=converttoppk&name={$key}&pass={$pass}&keep_file=1";

        $result = $this->cpanelapi($query );
        return $result ;
    }

    /**
     * create a subdomain
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SubDomain%3A%3Aaddsubdomain
     * @param string $account
     * @param string $dir
     * @param string $rootdomain
     * @param string $subdomain
     */
    public function create_subdomain($subdomain, $dir,$rootdomain='',$account='') {
        if(!$account) $account = $this->cpanelUser;
        if(!$rootdomain) $rootdomain = $this->domain;

        $query = "/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SubDomain&cpanel_jsonapi_func=addsubdomain&domain={$subdomain}&rootdomain={$rootdomain}&dir={$dir}";

        $result = $this->cpanelapi($query );
        return $result ;
    }

    /**
     * delete a subdomain
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SubDomain%3A%3Adelsubdomain
     * @param $subdomain
     * @param string $account
     * @param string $rootdomain
     * @param string $subdomain
     */
    public function del_subdomain($subdomain, $rootdomain='',$account='') {
        if(!$account) $account = $this->cpanelUser;
        if(!$rootdomain) $rootdomain = $this->domain;

        $domain = "{$subdomain}.{$rootdomain}";
        $query = "/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SubDomain&cpanel_jsonapi_func=delsubdomain&domain={$domain}";

        $result = $this->cpanelapi($query );
        return $result ;
    }

    /**
     * list all subdomains from account
     * https://documentation.cpanel.net/display/SDK/cPanel+API+2+Functions+-+SubDomain%3A%3Alistsubdomains
     * @param string $account
     */
    public function list_subdomains($account='') {
        if(!$account) $account = $this->cpanelUser;

        $query ="/json-api/cpanel?cpanel_jsonapi_user={$account}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=SubDomain&cpanel_jsonapi_func=listsubdomains";
        $result = $this->cpanelapi($query );
        return $result ;
    }
}
/**
 * Class HW_CPanel_Utilities
 */
class HW_CPanel_Utilities {
    public $host;
    public $domain;
    public $cpaneluser;
    public $cpaneluser_pass;
    public $acc_id;
    public $email_domain;
    //params
    public $params = array();

    /**
     * @param $acc
     * @param array $params
     */
    public function __construct($acc, $params = array()) {
        $acc_id = $acc;   //get default account=8 (dayhocwordpress.com);
        if(empty($acc_id)) {
            $acc_id = 8;    //get default account=8 (dayhocwordpress.com)
        }
        $this->acc_id = $acc_id;   //get default account=8 (dayhocwordpress.com);
        //get cpanel acc
        $acc = get_cpanel_acc($acc_id);
        $this->host = isset($acc['cpanel_host'])? $acc['cpanel_host']: '';
        $this->domain = isset($acc['cpanel_domain'])? $acc['cpanel_domain']: '';
        $this->cpaneluser = isset($acc['cpanel_user'])? $acc['cpanel_user']: HW_WHM_ROOT_USER;
        $this->cpaneluser_pass= isset($acc['cpanel_pass'])? decrypt($acc['cpanel_pass']) :HW_WHM_ROOT_PASS;
        $this->email_domain= isset($acc['cpanel_email'])? $acc['cpanel_email']: 'laptrinhweb123@gmail.com';

        //get params
        if(is_array($params)) $this->params = $params;
        //pre ajax hooks
        $this->setup_hooks();
    }
    private function setup_hooks() {
        add_action('ajax_task_generate_strong_pass', array($this, 'generate_strong_pass'));
        add_action('ajax_task_decpass', array($this, 'decpass'));
        add_action('ajax_task_scripts', array($this, 'print_scripts'));
    }

    /**
     * run command line
     * @param string $cmd
     */
    public function run_command($cmd='') {
        if(!$cmd) $cmd = _post('cmd');
        $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__ssh-cpanel.bat '.$this->acc_id.' '.$cmd.'"');
        ajax_output($str);
    }

    /**
     * run shell scrit as root
     * @param string $cmd
     * @param $ssh_batch
     */
    public function run_command_as_root($ssh_batch ='', $cmd='') {
        if(!$cmd) $cmd = _post('cmd');
        if(!$ssh_batch) $ssh_batch = 'ssh-root.bat';

        $str = exec('start cmd.exe /c "cd '.WHM_CPANEL_SHELL_APP.'\x_ssh/&&__ssh-root.bat '.$this->acc_id.' '.$ssh_batch.' '.$cmd.' '.HW_WHM_ROOT_USER.' '.HW_WHM_ROOT_PASS.' '.$this->cpaneluser.'"');
        ajax_output($str);
    }
    /**
     * generate password
     * @param $cp
     */
    function generate_strong_pass($cp) {
        #$cpanel = new HW_CPanel_Mysql($host, $cpaneluser, $cpaneluser_pass);
        #$t=$cpanel->xmlapi->api1_query($cpaneluser, 'Mysql','userdb' ,array('db4'));print_r($t);
        $available_sets = _req('available_sets', 'luds');
        echo generateStrongPassword(STRONG_PASS_LENGTH, false, $available_sets);
    }

    /**
     * print script file
     * @param $cp
     */
    function print_scripts($cp) {
        header('Content-Type: application/javascript');
        $vars = array(
            'ROOT_DIR' => ROOT_DIR
        );
        echo 'var __hwcpanel='.json_encode($vars);
    }
    /**
     * decode hash string
     * @param $cp
     */
    function decpass($cp) {
        echo decrypt(_req('str'));
    }
    public function db() {
        global $DB;
        return $DB;
    }

    /**
     * @param $name
     * @param $default
     */
    public function param($name, $default='') {
        return isset($this->params[$name])? $this->params[$name]: $default;
    }
    /**
     * @param $name
     * @return HW_CPanel_Mysql
     */
    public function get_instance($name) {
        static $instances = array();
        if($name =='mysql' && !isset($instances[$name])) {
            $instances[$name]= new HW_CPanel_Mysql($this->host, $this->cpaneluser, $this->cpaneluser_pass);
        }
        elseif($name== 'user' && !isset($instances[$name])) {
            $instances[$name]= new HW_CPanel_User($this->host, $this->cpaneluser, $this->cpaneluser_pass);
        }
        elseif($name =='fileman' && !isset($instances[$name])) {
            $instances[$name]= HW_CPanel_Fileman::loadacct($this->acc_id,true);
        }
        elseif($name =='fileman_no_auth' && !isset($instances[$name])) {
            $instances[$name]= HW_CPanel_Fileman::loadacct($this->acc_id,false);
        }

        elseif($name =='fileman1' && !isset($instances[$name])) {
            $instances[$name]=  new HW_CPanel_Fileman($this->host, $this->cpaneluser, $this->cpaneluser_pass);
        }
        elseif($name =='cpanel' && !isset($instances[$name])) {
            $instances[$name]= HW_CPanel::loadacct_instance($this->acc_id, false);
        }
        elseif($name =='ftp' && !isset($instances[$name])) {
            $instances[$name]= new HW_CPanel_Ftp($this->host, $this->cpaneluser, $this->cpaneluser_pass);
        }
        return isset($instances[$name])? $instances[$name] : null;
    }
    /**
     * upload tool
     * @param $acc_id
     * @param $subdomain
     * @param $runfile located in ../tmp/ folder
     * @return mixed
     */
    function upload_wptool($acc_id='', $subdomain='', $runfile='') {
        if(!$acc_id) $acc_id = $this->acc_id;
        #$subdomain = _post('subdomain');
        $upload_path = '/public_html/'.$subdomain;
        if(empty($runfile)) $runfile = HW_WP_TOOL_FILE;

        $file = array(
            'fullpath' => file_exists($runfile)? $runfile : ROOT_DIR. "/tmp/{$runfile}",
            'type' => 'text/plain',
            'name' => $runfile
        );
        $cpanel_file = HW_CPanel_Fileman::loadacct($acc_id, true);  //$this->get_instance('fileman');//
        $res =$cpanel_file->uploadfiles($file, $upload_path);
        return $res;
    }
    /**
     * list all svn users from `svn_wp_users` on server
     * @param $acc_id
     * @param string $subdomain
     * @return array
     */
    function wptool_list_svn_users($acc_id='', $subdomain ='') {
        if(!$acc_id) $acc_id = $this->acc_id;
        $acc = get_cpanel_acc($acc_id);
        $domain = isset($acc['cpanel_domain'])? $acc['cpanel_domain']: '';
        if(empty($domain)) {
            return ;
        }
        //update wp tool
        $res = $this->upload_wptool($acc_id, $subdomain);
        ajax_output($res);

        //run this file
        $url = rtrim($domain, '/') . '/'.HW_WP_TOOL_FILE.'?do=list_svn_users&json=1&acc='.$acc_id;
        $res = curl_post($url, array('subdomain'=> $subdomain));

        return (array)json_decode($res);
    }
}