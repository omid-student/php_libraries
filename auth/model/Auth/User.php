<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    /**
     * user can login with sms confirmation

     * ### Note: create table for user with pid,mobile,ip fields
     *
     * create table in database for sms limitation
     * ```
     * DROP TABLE IF EXISTS `tbl_sms_log`;
     * CREATE TABLE IF NOT EXISTS `tbl_sms_log` (
     * `pid` int(11) NOT NULL AUTO_INCREMENT,
     * `ip` varchar(100) COLLATE utf8_bin NOT NULL,
     * `mobile` varchar(11) COLLATE utf8_bin NOT NULL,
     * `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`pid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
     * ```
     * @uses Sms library
     * @uses auth config
     * @uses sms config
     *
     * @property $table string user table name(admin,user or other)
     * @property $length int define confirmation code length
     * @property $force_register bool user have to be registered already in database
     * @property $force_session bool use session for check user token
     * @property $force_update_keys bool add or update tbl_keys for restful api
     */
    class User extends CI_Model {

        const NOT_FOUND_USER    =   0;
        const NOT_REGISTER_USER =   0;
        const DISABLE_USER      =   0;
        const LIMITED_SMS       =   -1;
        const INVALID_MOBILE    =   -2;
        const INVALID_CODE      =   -3;
        const WRONG_CODE        =   -4;
        const EXPIRE_TOKEN      =   -5;

        protected $force_register;
        protected $force_session;
        protected $update_keys;

        function __construct() {
            parent::__construct();
            $this->load->config('auth');
            $this->load->helper('authorization');
            $this->load->helper('jwt');
            $this->load->library('session');
            $this->load->library('Sms');
            $this->load->config('jwt');
        }

        function __set($name, $value) {

            if ($name == 'table')
                $this->config->set_item('auth_table',$value);

            else if ($name == 'length')
                $this->config->set_item('confirmation_code_range',$value);

            else if ($name == 'force_register')
                $this->force_register   =   $value;

            else if ($name == 'force_session')
                $this->force_session    =   $value;

            else if ($name == 'force_update_keys')
                $this->update_keys    =   $value;

        }

        function login($mobile) {

            if (!preg_match('/\d{11}/',$mobile))
                return self::INVALID_MOBILE;

            $check_mobile   =   $this->check_mobile($mobile);

            if ($this->force_register == TRUE) {
                if ($check_mobile == FALSE)
                    return self::NOT_FOUND_USER;
            }

            if ($check_mobile != FALSE) {

                if ($check_mobile->status == 0)
                    return self::DISABLE_USER;

            }

            if ($this->is_limit_sms($mobile) == TRUE)
                return self::LIMITED_SMS;

            $code               =   $this->generate_code();

            $data               =   array();
            $data['mobile']     =   $mobile;
            $data['timestamp']  =   time();
            $data['code']       =   $code;

            $this->config->set_item('token_timeout',$this->confirm('code_expiration')); //set jwt expiration
            $token  =   AUTHORIZATION::generateToken($data);

            if ($this->force_session) {
                $this->session->set_userdata('token', $token);
                $this->session->mark_as_temp('token', config_item('code_expiration'));
            }

            $this->sms->to      =   $mobile;
            $this->sms->text    =   $code;
            $this->sms->driver  =   config_item('sms_driver');
            $this->sms->send();

            if (ENVIRONMENT == 'development')
                file_put_contents('sms.txt',$code);

            return $token;


        }

        /**
         *
         * @param $code
         * @param string $token
         * @return int|mixed|string jwt token
         */
        function confirm($code,$token = '') {

            if (!preg_match('/\d{4}/',$code))
                return self::INVALID_CODE;

            if ($this->force_session) {
                $token = $this->session->userdata('token');
            }

            $token_data = AUTHORIZATION::validateTimestamp($token);

            if ($token_data == FALSE)
                return self::EXPIRE_TOKEN;

            if ($code != $token_data->code)
                return self::WRONG_CODE;
            else {

                $check_mobile = $this->check_mobile($token_data->mobile);

                $this->session->unset_userdata('token');

                if ($check_mobile) {

                    $field_name     =   config_item('auth_pid_field_name');
                    $user_id        =   $check_mobile->{$field_name};

                    $token          =   $this->make_token($user_id, $token_data->mobile);

                } else {

                    if ($this->force_register == TRUE) {
                        return self::NOT_REGISTER_USER;
                    } else {

                        $this->db->set('mobile', $token_data->mobile);
                        $this->db->set('ip', ip2long($this->input->ip_address()));
                        $this->db->insert(config_item('auth_table'));

                        $user_id    =   $this->db->insert_id();
                        $token      =   $this->make_token($user_id, $token_data->mobile);

                    }

                }

                if ($this->db->where('user_id',$user_id)->get('keys')->num_rows() == 0) {
                    $this->db->set('user_id', $user_id);
                    $this->db->set('key', $token);
                    $this->db->set('level', 2);
                    $this->db->set('date_created', time());
                    $this->db->insert('keys');
                } else {
                    $this->db->where('user_id', $user_id);
                    $this->db->set('key', $token);
                    $this->db->set('date_created', time());
                    $this->db->update('keys');
                }

                $data               =   array();
                $data['ip']         =   $this->input->ip_address();
                $data['time']       =   time();
                $data['type']       =   config_item('auth_table');
                $data['token']      =   $token;
                $data['is_login']   =   TRUE;

                if (config_item('token_expiration') > 0) {
                    $this->session->set_userdata($data);
                    $this->session->mark_as_temp('token', config_item('token_expiration'));
                }

                return $token;

            }

        }

        /**
         * check user is login and exist token
         * @return bool|object
         */
        function check_login() {

            if (!$this->session->userdata('is_login'))
                return FALSE;

            $token  =   $this->session->userdata('token');
            $data   =   AUTHORIZATION::validateToken($token);

            if ($data == FALSE)
                return FALSE;

            return TRUE;

        }

        /**
         * extract user id from token
         * @param string $token
         * @return int
         */
        function get_user_id($token = '') {

            if ($this->force_session) {
                $token = $this->session->userdata('token');
            }

            $token_data = AUTHORIZATION::validateToken($token);

            if ($token_data == FALSE)
                return self::EXPIRE_TOKEN;

            return $token_data->user_id;

        }

        /**
         * sign out user and clear session
         */
        function sign_out() {

            $this->session->unset_userdata('token');
            $this->session->unset_userdata('is_login');
            $this->session->sess_destroy();

        }

        //check mobile exist in database or not
        function check_mobile($mobile) {

            $table_name =   config_item('auth_table');
            $result     =   $this->db->where('mobile',$mobile)->get($table_name);

            if ($result->num_rows() == 0)
                return FALSE;
            else
                return $result->row();

        }

        //check mobile exist in database or not
        function check_id($user_id) {

            $table_name =   config_item('auth_table');
            $result     =   $this->db->where('pid',$user_id)->get($table_name);

            if ($result->num_rows() == 0)
                return FALSE;
            else
                return $result->num_rows();

        }

        /**
         * update user fields with user id
         * @param $user_id
         * @param $fields contain array fields
         * @return int number of updated count
         */
        function edit($user_id,$fields) {

            $table_name =   config_item('auth_table');

            $this->db->db_debug = FALSE;
            $this->db->set((array)$fields);
            $this->db->where('pid', $user_id)->update($table_name);

            return $this->db->affected_rows();

        }

        function profile($user_id) {

            $table_name =   config_item('auth_table');

            $this->db->where('pid',$user_id);
            $result     =   @$this->db->get($table_name)->row();

            return $result;

        }

        //<editor-fold desc="Private functions">
        //check sms limitation for each IP
        private function is_limit_sms($mobile) {

            $ip     =   $this->input->ip_address();

            $this->db->group_start();
            $this->db->where('ip', $ip);
            $this->db->or_where('mobile', $mobile);
            $this->db->group_end();
            $this->db->where('UNIX_TIMESTAMP(`date`) > UNIX_TIMESTAMP(now() - INTERVAL 1 HOUR)');
            $sms_count = $this->db->get('sms_log')->num_rows();

            if ($sms_count > config_item('sms_limit')) {
                return TRUE;
            } else {
                $this->db->set('ip',$ip)->insert('sms_log');
                return FALSE;
            }

        }

        //generate random code with custom length
        private function generate_code() {

            $length =   config_item('confirmation_code_range');

            $result = '';

            for($i = 0; $i < $length; $i++) {
                $result .= mt_rand(0, 9);
            }

            return $result;

        }

        private function make_token($user_id,$mobile) {
            $this->config->set_item('token_timeout',$this->confirm('token_expiration')); //set jwt expiration
            return AUTHORIZATION::generateToken(array('user_id' => $user_id,'mobile' => $mobile));
        }
        //</editor-fold>

    }