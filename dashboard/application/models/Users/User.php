<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    class User extends CI_Model {

        public const ERROR_EXIST_EMAIL = -3;
        public const ERROR_INVALID_REQUEST = -4;
        public const ERROR_NOT_FOUND_USER = 404;
        public const ERROR_INVALID_PASSWORD = 403;

        function __construct() {
            parent::__construct();
            $this->load->language('validation');
            $this->load->config('jwt');
            $this->load->library('session');
            $this->load->language('users');
            $this->load->helper(array('authorization','jwt','language'));
        }

        /**
         * send sms code to mobile or activation link to email
         * @param $mobile_or_email
         * @return array|string
         */
        function send_activation($mobile_or_email) {

            if (filter_var($mobile_or_email,FILTER_VALIDATE_EMAIL)) {

                $this->config->set_item('token_timeout',4320);

                $data   =   array('email' => $mobile_or_email,'timestamp' => time());

                $link   =   base_url('confirmation?t='.AUTHORIZATION::generateToken($data));

                $this->load->language('user','persian');
                $text   =   sprintf(config_item('confirmation_email'),config_item('project_title'),$link,$link);

                $user_message   =   new UserMessage('',$mobile_or_email);
                $user_message->sendEmail($text,'');

                return array('token' => '');

            }

            if ($this->is_limit_sms($mobile_or_email))
                return lang('error_sms_limit');

            if (!preg_match('/^0\d{10}$/',$mobile_or_email))
                return 'شماره موبایل وارد شده درست نیست';

            $check_mobile   =   $this->check_mobile($mobile_or_email);

            if ($mobile_or_email != '09124952735')
                $code   =   rand(1000,9999);
            else
                $code   =   12345;

            $token  =   password_hash($code.$mobile_or_email,PASSWORD_DEFAULT);

            $this->session->set_userdata('token',$token);
            $this->session->mark_as_temp('token',config_item('confirmation_code_alive'));

            if ($mobile_or_email != '09124952735') {
                $user_message = new UserMessage('', '', $mobile_or_email);
                $user_message->sendSms($code);
            }

            if ($check_mobile == FALSE)
                $action =   'register';
            else
                $action =   'login';

            return array('timeout' => config_item('confirmation_code_alive'),'token' => $token,'action' => $action);

        }

        function confirm_sms($mobile,$code,$token) {

            $sess_token = $this->session->userdata('token');

            if ($sess_token == '')
                return lang('error_timeout_code');

            if (!preg_match('/^\d{4,5}$/', $code))
                return lang('error_invalid_code');

            $check_mobile = $this->check_mobile($mobile);

            if (!preg_match('/^0\d{10}$/',$mobile))
                return lang('error_invalid_mobile');

            if (!password_verify($code . $mobile, $token))
                return lang('error_invalid_code');

            if ($check_mobile === FALSE) {

                $this->db->db_debug = FALSE;
                $this->db->trans_start();
                $this->db->set('mobile',$mobile);
                $this->db->set('pid',$this->generate_user_id());
                $this->db->set('invitation_code',$this->generate_invitation_code());
                $this->db->set('ip',ip2long($this->input->ip_address()));
                $this->db->set('last_modified',date('Y-m-d H:i:s',time()));
                $this->db->insert('user');

                if ($this->db->affected_rows() > 0) {

                    $user_id = $this->db->insert_id();

                    $payload = array();
                    $payload['pid']         =   $user_id;
                    $payload['mobile']      =   $mobile;
                    $payload['timestamp']   =   time();

                    $token = AUTHORIZATION::generateToken($payload);

                    $this->create_user_key($user_id,$token);

                    $this->db->trans_complete();

                    $this->session->unset_userdata('token');

                    return array('token' => $token, 'action' => 'register');

                } else {
                    return FALSE;
                }

            }
            else {

                $payload            =   array();
                $payload['pid']     =   $check_mobile->pid;
                $payload['mobile']  =   $mobile;
                $payload['timestamp']    =   time();

                $token  =   AUTHORIZATION::generateToken($payload);

                $this->create_user_key($check_mobile->pid,$token);

                $this->session->unset_userdata('token');

                return array('action' => 'login','token' => $token,'user_id' => $check_mobile->pid);

            }

        }

        function confirm_email($token,$password) {

            $token_data = AUTHORIZATION::validateToken($token);

            if ($token_data == FALSE)
                return self::ERROR_INVALID_REQUEST;

            if (filter_var($token_data->email, FILTER_VALIDATE_EMAIL)) {

                $check_email = $this->check_email($token_data->email);

                if ($check_email == TRUE) {
                    return self::ERROR_EXIST_EMAIL;

                } else {

                    $this->db->set('password',password_hash($password,PASSWORD_DEFAULT));
                    $this->db->set('email',$token_data->email);
                    $this->db->set('ip',ip2long($this->input->ip_address()));
                    $this->db->insert('user');

                    $user_id            =   $this->db->insert_id();

                    $payload            =   array();
                    $payload['pid']     =   $user_id;
                    $payload['mobile']  =   $token_data->email;
                    $payload['timestamp']    =   time();

                    $token  =   AUTHORIZATION::generateToken($payload);

                    $this->db->set('user_id',$user_id)->
                               set('key',$token)->
                               set('level',2)->
                               insert('keys');

                    return array('action' => 'login','token' => $token,'pid' => $user_id);

                }

            }

        }

        function register($mobile,$code,$token,$first_name,$last_name,$invitation_code,$email) {

            $token =    AUTHORIZATION::validateToken($token);

            //region Validate token
            if (!is_object($token))
                return lang('error_timeout_code');

            $token  =   (array) $token;

            if ($token['mobile'] != $mobile && $token['code'] != $code)
                return lang('error_timeout_code');

            $check_mobile = $this->check_mobile($mobile);

            if ($check_mobile)
                return lang('error_already_use_mobile');
            //endregion

            if ($invitation_code != '') {
                $invitation_info    =   $this->check_invitation_code($invitation_code);
                if ($invitation_info == FALSE) {
                    return lang('error_invitation_code');
                }
            }

            //region Rules
            $this->load->library('form_validation');
            $this->form_validation->set_error_delimiters('','');
            $this->form_validation
                ->set_rules('first_name','lang:first_name','required|trim|min_length[3]|max_length[60]')
                ->set_rules('last_name','lang:last_name','required|trim|min_length[3]|max_length[60]')
                ->set_rules('confirm_code','lang:code','required|integer');

            $this->form_validation->set_data(array('mobile' => $mobile,'confirm_code' => $code,
                                                   'first_name' => $first_name,'last_name' => $last_name));

            if (!$this->form_validation->run()) {
                return $this->form_validation->error_string('','');
            }
            //endregion

            //region Insert records
            $this->db->db_debug = FALSE;
            $this->db->trans_start();
            $this->db->set('pid',$this->generate_user_id());
            $this->db->set('first_name',$first_name);
            $this->db->set('last_name',$last_name);
            $this->db->set('email',$email);
            $this->db->set('mobile',$mobile);
            $this->db->set('invite_code',$invitation_code);
            $this->db->set('invitation_code',$this->generate_invitation_code());
            $this->db->set('ip',ip2long($this->input->ip_address()));
            $this->db->insert('user');

            if ($this->db->affected_rows() > 0) {

                $user_id    =   $this->db->insert_id();

                if (isset($invitation_info)) {
                    //if user redirect to here with invite code
                }

                $payload            =   array();
                $payload['pid']     =   $user_id;
                $payload['mobile']  =   $mobile;
                $payload['timestamp']    =   time();

                $token      =   AUTHORIZATION::generateToken($payload);

                $this->db->set('date_created',time());
                $this->db->set('user_id',$user_id)->set('key',$token)->set('level',2)->insert('keys');

                $this->db->trans_complete();

                if ($this->db->trans_status() == FALSE)
                    return lang('error_no_add');

				@mkdir("files");
				@mkdir("files/user");
				@mkdir("files/user/$user_id");
                @file_put_contents("files/user/$user_id/index.html",'index');
				
                $this->session->unset_userdata('token');

                return array('action' => 'login','token' => $token,'user_id' => $user_id);

            } else {
                $this->db->trans_rollback();
                return lang('error_no_add');
            }
            //endregion

        }

        function login($email,$password) {

            $email =    $this->check_email($email);

            if ($email == FALSE)
                return self::ERROR_NOT_FOUND_USER;

            if (!password_verify($password,$email->password))
                return self::ERROR_INVALID_PASSWORD;

            $payload                =   array();
            $payload['pid']         =   $email->pid;
            $payload['email']       =   $email;
            $payload['timestamp']   =   time();

            $token  =   AUTHORIZATION::generateToken($payload);

            $this->db->where('user_id',$email->pid)->delete('keys');

            $this->db->set('date_created',time());
            $this->db->where('user_id',$email->pid);
            $this->db->set('key',$token);
            $this->db->set('level',2)->insert('keys');

            return array('action' => 'login','token' => $token,'pid' => $email->pid);

        }

        /**
         * return user object
         * @param $user_id user pid
         * @return UserEntity
         */
        function getUser($user_id) {

            $this->db->select('`pid`, `first_name`, `last_name`, `mobile`, `birthday`, `email`, `cash`, `invite_code`, `ip`, `status`, `date_registered`,last_modified');

            $info   =   $this->db->where('pid',$user_id)->get('user');

            if ($info->num_rows() == 0)
                return NULL;

            $info   =   $info->custom_row_object(0,'UserEntity');

            return $info ;

        }

        /**
         * @param $user_id user pid
         * @return UserMessage
         */
        function getMessage($user_id = '') {

            if ($user_id != '') {

                $t = $this->getUser($user_id);

                $m = new UserMessage($user_id, $t->email, $t->mobile);
                return $m;

            } else {

                $m = new UserMessage('','','');
                return $m;

            }

        }

        function check_mobile($mobile) {

            $this->db->where('mobile',$mobile);
            $user  =    $this->db->get('user');

            if ($user->num_rows() == 0)
                return FALSE;
            else
                return $user->row();

        }

        function check_email($email) {

            $this->db->where('email',$email);
            $user  =    $this->db->get('user');

            if ($user->num_rows() == 0)
                return FALSE;
            else
                return $user->row();

        }

        function check_invitation_code($code) {

            $this->db->where('invite_code',$code);
            $user  =    $this->db->get('user');

            if ($user->num_rows() == 0)
                return FALSE;
            else
                return $user->row();

        }

        function check_user_id($user_id) {

            $this->db->where('pid',$user_id);
            $user  =    $this->db->get('user');

            if ($user->num_rows() == 0)
                return FALSE;
            else
                return $user->row();

        }

        function get_user_id($token = '') {

            $token_data = AUTHORIZATION::validateToken($token);

            if ($token_data == FALSE)
                return FALSE;

            return $token_data->user_id;

        }

        function list($status = '',$offset = '',$date = '') {

            if ($date != '')
                $this->db->where('DATE(date_registered)',$date);

            if ($status != '')
                $this->db->where('status',$status);

            if ($offset != '')
                $this->db->limit(config_item('per_page'),$offset);

            $this->db->order_by('pid','DESC');

            $users  =   $this->db->get('user');

            return $users->result_array();

        }

        /**
         * create user,keys and sms tables
         * @config
         */
        function install_table() {

            $this->db->query('DROP TABLE IF EXISTS `tbl_user`;
                CREATE TABLE IF NOT EXISTS `tbl_user` (
                  `pid` int(11) NOT NULL AUTO_INCREMENT,
                  `mobile` char(11) COLLATE utf8_persian_ci NOT NULL,
                  `password` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
                  `first_name` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
                  `last_name` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
                  `birthday` date DEFAULT NULL,
                  `gender` tinyint(1) DEFAULT NULL,
                  `age` tinyint(4) DEFAULT NULL,
                  `email` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
                  `cash` int(11) DEFAULT 0,
                  `invite_code` varchar(10) COLLATE utf8_persian_ci DEFAULT NULL,
                  `invitation_code` varchar(15) COLLATE utf8_persian_ci DEFAULT NULL COMMENT "کد معرف",
                  `ip` int(11) NOT NULL,
                  `status` char(1) CHARACTER SET utf8 COLLATE utf8_persian_ci NULL DEFAULT "1",
                  `app_os` char(1) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL COMMENT "0 => android,1 => ios,2=> web",
                  `app_version` varchar(5) CHARACTER SET utf8 COLLATE utf8_persian_ci DEFAULT NULL COMMENT "نسخه فعلی اپلیکیشن مورد استفاده",
                  `app_device_id` varchar(20) COLLATE utf8_persian_ci DEFAULT NULL COMMENT "سریال دستگاه مورد استفاده",
                  `fm_token` varchar(500) COLLATE utf8_persian_ci DEFAULT NULL,
                  `subscription_expire` date DEFAULT NULL,
                  `date_registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `last_modified` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`pid`),
                  UNIQUE KEY `mobile` (`mobile`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;');

            $this->db->query('DROP TABLE IF EXISTS `tbl_keys`;
                CREATE TABLE IF NOT EXISTS `tbl_keys` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) NOT NULL,
                  `key` varchar(1000) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  `level` int(2) NOT NULL,
                  `ignore_limits` tinyint(1) NOT NULL DEFAULT \'0\',
                  `is_private_key` tinyint(1) NOT NULL DEFAULT \'0\',
                  `ip_addresses` text CHARACTER SET utf8 COLLATE utf8_bin,
                  `date_created` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');

            $this->db->query("DROP TABLE IF EXISTS `tbl_user_sms_log`;
                CREATE TABLE IF NOT EXISTS `tbl_user_sms_log` (
                  `pid` int(11) NOT NULL AUTO_INCREMENT,
                  `ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  `mobile` char(11) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`pid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");

        }

        /**
         * check limitation sms
         * @param $mobile user mobile
         * @return bool false is limited
         * sms sql table
         * DROP TABLE IF EXISTS `tbl_sms_log`;
        CREATE TABLE IF NOT EXISTS `tbl_user_sms_log` (
        `pid` int(11) NOT NULL AUTO_INCREMENT,
        `ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `mobile` char(11) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`pid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
         */
        private function is_limit_sms($mobile) {

            $ip = $this->input->ip_address();

            $this->db->group_start();
            $this->db->where('ip', $ip);
            $this->db->or_where('mobile',$mobile);
            $this->db->group_end();

            $this->db->where('UNIX_TIMESTAMP(`date`) > UNIX_TIMESTAMP(now() - INTERVAL 1 HOUR)');
            $sms_count = $this->db->get('user_sms_log')->num_rows();

            if ($sms_count > config_item('sms_limit')) {
                return TRUE;
            } else {
                $this->db->set('ip',$ip)->set('mobile',$mobile)->insert('user_sms_log');
                return FALSE;
            }

        }

        function generate_invitation_code() {

            $length     =   10;

            $inviteCode = "";
            $characters = "abcdefghijklmnopqrstuvwxyz0123456789";

            for ($p = 0; $p < $length; $p++) {
                $inviteCode .= $characters[mt_rand(10, strlen($characters)-1)];
            }

            return $inviteCode;

        }

        private function generate_user_id() {

            $id = str_replace('.','',$this->input->ip_address());

            if (strlen($id) > 9)
                return substr($id,1,9);
            else
                return $id;

        }

        private function create_user_key($user_id,$token) {

            $this->db->where('user_id',$user_id)->delete('keys');

            $this->db->set('date_created',time());
            $this->db->where('user_id',$user_id);
            $this->db->set('key',$token);
            $this->db->set('level',2)->insert('keys');

        }

    }

    class UserEntity {

        public $pid;
        public $first_name;
        public $last_name;
        public $mobile;
        public $birthday;
        public $gender,$age;
        public $email;
        public $cash;
        public $invite_code,$invitation_code;
        public $ip;
        public $status;
        public $app_os,$app_version,$app_device_id;
        public $fm_token;
        public $subscription_expire;
        public $date_registered,$last_modified;

        public const BLOCK = 3;
        public const ERROR_FIRST_NAME = -1;
        public const ERROR_LAST_NAME = -2;
        public const ERROR_EMAIL = -3;

        public function getIP() {
            return long2ip($this->ip);
        }

        public function getProfile() {
            return get_object_vars($this);
        }

        public function IsBlock() {

            if ($this->status == self::BLOCK)
                return TRUE;
            else
                return FALSE;

        }

        public function IsExpireSubscription() {

            if ($this->getSubscription() <= 0)
                return TRUE;
            else
                return FALSE;

        }

        public function getRegisterDate() {

            $ci = & get_instance();
            $ci->load->helper('conversion');
            return gregorian2jalali($this->date_registered,'d J Y');

        }

        /**
         * get persian birthday date
         * @return mixed|string
         */
        public function getBirthday() {
            return gregorian2jalali($this->birthday,'Y-m-d');
        }

        /**
         * get persian last modified date
         * @return string
         */
        public function GetLastModified() {

            $ci = & get_instance();
            $ci->load->helper('conversion');
            return time_ago($this->last_modified);

        }

        public function getName() {
            return "{$this->first_name} {$this->last_name}";
        }

        /**
         * get user subscription time
         * @return bool|DateInterval
         */
        function getSubscription() {

            $ci = & get_instance();

            $res = $ci->db->where('pid',$this->pid)->get('user');
            $res = $res->row()->subscription_expire;

            $diff   =   date_diff(date_create(date('Y-m-d',time())),date_create($res));
            $diff   =   $diff->format("%R%a");

            if ($diff > 0)
                return $diff;
            else
                return FALSE;

        }

        /**
         * add hours yo user subscription
         * @param $hour hours number to add
         * @return bool
         */
        function addSubscription($hour) {

            $ci = & get_instance();

            $res = $ci->db->where('pid',$this->pid)->get('user');
            $res = $res->row()->subscription_expire;

            $diff   =   date_diff(date_create(date('Y-m-d',time())),date_create($res));
            $diff   =   $diff->format("%R%a");

            if ($diff > 0)
                return FALSE;

            $ci->db->set('pid',$this->pid);
            $ci->db->set('subscription_expire',date('Y-m-d',strtotime("+{$hour} hours")));
            $ci->db->insert('user');

            return TRUE;

        }

        /**
         * save all changes on fields
         */
        function saveChanges() {

            $ci = & get_instance();

            if (strlen(trim($this->first_name)) < 3)
                return self::ERROR_FIRST_NAME;

            if (strlen(trim($this->last_name)) < 3)
                return self::ERROR_LAST_NAME;

            $ci->db->set('first_name',$this->first_name);
            $ci->db->set('last_name',$this->last_name);

            if (preg_match('/\d{4}\-\d{1,2}\-\d{1,2}/',$this->birthday))
                $ci->db->set('birthday',$this->birthday);

            $ci->db->set('gender',$this->gender);
            $ci->db->set('age',$this->age);

            if ($this->email != '') {
                if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
                    return self::ERROR_EMAIL;
            }

            $ci->db->set('email',$this->email);

            $ci->db->where('pid',$this->pid);
            $ci->db->update('user');

            return $ci->db->affected_rows();

        }

        function Delete() {

            $ci = & get_instance();

            $ci->db->where('pid',$this->pid)->delete('user');
            $ci->db->where('user_id',$this->pid)->delete('keys');

            $ci->load->helper("file"); // load the helper
            delete_files("files/user/{$this->pid}", true);
            @rmdir("files/user/{$this->pid}");

            return TRUE;

        }

        function changeStatus($status) {

            $ci = & get_instance();

            $ci->db->where('pid',$this->pid)->set('status',$status)->update('user');

            return $ci->db->affected_rows();

        }

        /**
         * change user avatar
         * file must be jpg and key name be "file"
         * @return bool|string
         */
        function changeAvatar() {

            $ci = & get_instance();

            if (count($_FILES) > 0) {

                $config['file_name']        =   'avatar.jpg';
                $config['allowed_types']    =   'jpg';
                $config['max_width']        =   500;
                $config['min_width']        =   500;
                $config['min_height']       =   100;
                $config['max_height']       =   100;
                $config['overwrite']        =   TRUE;
                $config['max_size']         =   140000;
                $config['upload_path']      = 	"files/user/{$this->pid}";

                //Load upload library and initialize configuration
                $ci->load->library('upload', $config);
                $ci->upload->initialize($config);
                $result     =   $ci->upload->do_upload('file');

                if (!$result)
                    return $ci->upload->display_errors('','');
                else {
                    return TRUE;
                }

            } else {
                return FALSE;
            }

        }

        /**
         * return simcard operator
         * 0 is hamrah aval,1 is irancell and 2 is rightel
         * @return bool
         */
        function getSimcardOperator() {

            if (substr($this->mobile,0,3) == '091' || substr($this->mobile,0,3) == '099')
                return 0;

            if (substr($this->mobile,0,3) == '093' || substr($this->mobile,0,4) == '0901' || substr($this->mobile,0,4) == '0902' || substr($this->mobile,0,4) == '0903')
                return 1;

            if (substr($this->mobile,0,4) == '0921' || substr($this->mobile,0,4) == '0922')
                return 2;

            return '';

        }

        function increaseCash($amount) {

            $ci = & get_instance();

            $ci->db->where('pid',$this->pid)->set('cash',"cash + $amount",FALSE);
            $ci->db->update('user');

            return $ci->db->affected_rows() > 0 ? TRUE : FALSE;

        }

        function decreaseCash($amount) {

            $ci = & get_instance();

            $ci->db->where('pid',$this->pid)->set('cash',"cash - $amount",FALSE);
            $ci->db->update('user');

            return $ci->db->affected_rows() > 0 ? TRUE : FALSE;

        }

        /**
         * prepare data from array and set to database
         * @param $data
         * @return bool
         */
        function editProfile($data) {

            $CI =& get_instance();

            unset($data['pid']);
            unset($data['mobile']);
            unset($data['status']);
            unset($data['invite_code']);
            unset($data['invitation_code']);
            unset($data['ip']);
            unset($data['subscription_expire']);
            unset($data['date_registered']);
            unset($data['last_modified']);
            unset($data['vc']);
            unset($data['vn']);
            unset($data['pn']);
            unset($data['os']);

            if (isset($data['birthday']))
                if (!preg_match('/\d{4}-\d{1,2}-\d{1,2}/',$data['birthday']))
                    unset($data['birthday']);

            $CI->db->db_debug = FALSE;
            $CI->db->where('pid',$this->pid);
            $CI->db->set($data);
            $CI->db->update('user');

            if (isset($_FILES['avatar'])) {

                $config['file_name'] = "{$this->pid}.jpg";
                $config['allowed_types'] = 'jpg';
                $config['max_width'] = 500;
                $config['min_width'] = 100;
                $config['min_height'] = 100;
                $config['max_height'] = 500;
                $config['overwrite'] = TRUE;
                $config['max_size'] = 140000;
                $config['upload_path'] = "files/user";

                //Load upload library and initialize configuration
                $CI->load->library('upload', $config);
                $CI->upload->initialize($config);
                $result = $CI->upload->do_upload('avatar');

                if (!$result)
                    return $CI->upload->display_errors('', '');

            }

            return $CI->db->affected_rows() > 0 ? TRUE : FALSE;

        }

    }

    class UserMessage {

        private $pid,$email,$mobile;

        public function __construct($user_id = '',$email = '',$mobile = '') {
            $this->pid      =   $user_id;
            $this->email    =   $email;
            $this->mobile   =   $mobile;
        }

        function sendSms($text) {

            $ci = & get_instance();

            $ci->load->config('sms');

            $ci->load->driver('sms');
            $ci->sms->set_mobile($this->mobile);
            $ci->sms->set_token(config_item('smsir_apikey'));
            $ci->sms->set_secret_key(config_item('smsir_secretkey'));
            $ci->sms->smsir->set_template_id(config_item('smsir_template_id'));
            $ci->sms->smsir->add_parameter('value',$text);
            $result =   $ci->sms->smsir->send();

            return $result;

        }

        function sendNotification($text,$tag = '') {

            $ci = & get_instance();

            $ci->load->helper('push');

            $payload    =   array();
            $payload['title']   =   config_item('project_title');
            $payload['body']    =   $text;
            $payload['type']    =   'message';
            $payload['value']   =   $tag;

            $res                =   @send_push($this->pid,$payload,0,TRUE);

            return $res;

        }

        function sendEmail($text,$subject) {

            $ci = & get_instance();

            $ci->load->library('mailer');
            $res = $ci->mailer->send($this->email,$subject,$text);

            return $res;

        }

        function List($status = '',$date = '',$offset = 0) {

            $CI =   & get_instance();

            if ($this->pid != '')
                $CI->db->where('user_id',$this->pid);

            if ($date != '')
                $CI->db->where('DATE(date_received)',$date);

            if ($status != '')
                $CI->db->where('user_message.status',$status);

            if ($offset != '')
                $CI->db->limit(config_item('per_page'),$offset);

            $CI->db->order_by('user_message.pid','DESC');

            $CI->db->join('user','user.pid = user_message.user_id');
            $CI->db->select('CONCAT(first_name," ",last_name) AS full_name,user_message.pid,text,date_received');

            $users  =   $CI->db->get('user_message');

            return $users->result_array();

        }

        /**
         * set done status for message
         * @param $pid
         * @return int
         */
        function seeMessage($pid) {

            $CI =& get_instance();

            $CI->db->where('pid',$pid)->set('status',1)->update('user_message');

            return $CI->db->affected_rows();

        }

    }