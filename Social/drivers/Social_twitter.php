<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Social_twitter extends CI_Driver {

        private $ci;
        private $parent;

        function __construct() {
            $this->ci       =	& get_instance();
            $this->parent   =	$this->ci->social;
            require_once APPPATH.'libraries/social/drivers/files/twitter.php';
        }

        function send() {

            \Codebird\Codebird::setConsumerKey($this->parent->token['token_1'],$this->parent->token['token_2']);
            $cb = \Codebird\Codebird::getInstance();
            $cb->setToken($this->parent->token['token_3'],$this->parent->token['token_4']);

            if ($this->parent->file_path != '') {
                $params = array(
                    'status' => $this->parent->caption,
                    'media[]' => $this->parent->file_path
                );
            }
            else {
                $params = array(
                    'status' => $this->parent->caption
                );
            }

            $reply = $cb->statuses_updateWithMedia($params);

        }

    }