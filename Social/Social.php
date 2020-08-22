<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * post to social media easily
     *
     * for telegram social network you have to follow these steps:
     * go to @botfather in telegram
     * type /newbot and enter name for robot with prefix _bot
     * then you can see token then follow below link
     * https://api.telegram.org/bot{YOUR_TOKEN}/getUpdates
     *
     * for twitter social network,you have to add token according below series to array
     * CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET
     *
     * @property $token array|var social app token
     * @property $file_path attach media
     * @property $caption caption post
     */
    class Social extends CI_Driver_Library {

        private $CI;
		private $current_child;
        private $file_path;
        private $caption;
        private $token;

        function __construct() {
            $this->CI = & get_instance();
            $this->valid_drivers = array('facebook','twitter','telegram');
        }

        //token can be array
        //for twitter,use array
        function set_token($token) {
            $this->token  =   $token;
            return $this;
        }

        function set_media_file($file_path) {
            $this->file_path    =   $file_path;
            return $this;
        }

        function set_caption($caption) {
            $this->caption    =   $caption;
            return $this;
        }

        function __get($child) {

            if ($child == 'token')
                return $this->token;

            if ($child == 'caption')
                return $this->caption;

            if ($child == 'file_path')
                return $this->file_path;

            if (in_array($child,$this->valid_drivers)) {
                $ob                     =   $this->load_driver($child);
                $this->current_child    =   $ob;
                return $ob;
            }

        }

    }