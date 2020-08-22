<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Social_facebook extends CI_Driver {

        private $ci;
        private $parent;
        private $post_url = 'https://graph.facebook.com/1755455501393338/feed';

        function __construct() {
            $this->ci       =	& get_instance();
            $this->parent   =	$this->ci->social;
        }

        function send() {

           $data['picture']		    =   $this->parent->file_path;
           $data['link']            =   '';
           $data['message']		    =   $this->parent->caption;
           $data['caption']		    =   $this->parent->caption;
           $data['description']	    =   $this->parent->caption;
           $data['access_token']    =   $this->parent->token;

           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL, $this->post_url);
           curl_setopt($ch, CURLOPT_POST, 1);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
           $return = curl_exec($ch);
           curl_close($ch);
           return $return;

        }

    }