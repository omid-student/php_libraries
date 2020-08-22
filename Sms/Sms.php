<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * send sms with different panels
     *
     * @property $token string panel token
     * @property $secret_key string use second token for panels have two token
     * @property $sender string sender phone number
     * @property $mobile string user mobile number
     * @property $text string sms body
     */
    class Sms extends CI_Driver_Library {

        private $CI;
        private $token,$secret_key;
        private $mobile;
        private $text;
		private $current_child;

        function __construct() {
            $this->CI = & get_instance();
            $this->valid_drivers = array('parsgreen','smsir');
        }

        function set_token($id) {
            $this->token  =   $id;
            return $this;
        }

        /**
         * set sms sender number
         * @param $phone
         * @return $this
         */
        function set_sender($phone) {
            $this->sender  =   $phone;
            return $this;
        }

        /**
         * use only for sms.ir server
         * @param $secret_key
         * @return $this
         */
        public function set_secret_key($secret_key) {
            $this->secret_key = $secret_key;
            return $this;
        }

        public function set_mobile($mobile) {
            $this->mobile = $mobile;
            return $this;
        }

        public function set_text($text) {
            $this->text = $text;
            return $this;
        }

        function __get($child) {

            if (in_array($child,$this->valid_drivers)) {
                $ob                     =   $this->load_driver($child);
                $this->current_child    =   $ob;
                return $ob;
            } else {
                if ($child == 'mobile')
                    return $this->mobile;
                else if ($child == 'token')
                    return $this->token;
                else if ($child == 'text')
                    return $this->text;
                else if ($child == 'secret_key')
                    return $this->secret_key;
                else
                    return;
            }

        }

    }