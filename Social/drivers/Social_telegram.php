<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Social_telegram extends CI_Driver {

        private $ci;
        private $parent;
        private $channel_id = '@';

        function __construct() {
            $this->ci       =   & get_instance();
            $this->parent   =   $this->ci->social;
        }

        function set_channel_id($id) {
            $this->channel_id   =   $id;
            return $this;
        }

        function send() {

            if ($this->parent->file_path != '') {

                $mime = mime_content_type($this->parent->file_path);

                if(strstr($mime, "video/")) {
                    $this->SendVideo();
                } else if(strstr($mime, "image/")) {
                    $this->SendPhoto();
                } else if(strstr($mime, "audio/")) {
                    $this->SendAudio();
                }

            } else {
                $this->SendMessage();
            }

        }

        private function SendMessage() {

            $url = 'https://api.telegram.org/bot'.$this->parent->token.'/sendMessage';

            $data = array(
                'chat_id'=>$this->channel_id,
                'text'=>$this->parent->caption);

            $options=array('http'=>array('method'=>'POST','header'=>"Content-Type:application/x-www-form-urlencoded\r\n",'content'=>http_build_query($data),),);
            $context=stream_context_create($options);
            $result=file_get_contents($url,false,$context);
            return $result;

        }

        private function SendPhoto() {

            $BOT_TOKEN  = $this->parent->token; //----YOUR BOT TOKEN
            $chat_id    =   $this->channel_id; // or '123456' ------Receiver chat id

            define('BOTAPI', 'https://api.telegram.org/bot' . $BOT_TOKEN . '/');

            $cfile = new CURLFile(realpath($this->parent->file_path), 'image/jpg', $this->parent->file_path); //first parameter is YOUR IMAGE path

            $data = [
                'chat_id' => $chat_id,
                'photo' => $cfile,
                'caption' => $this->parent->caption
            ];

            $ch = curl_init(BOTAPI . 'sendPhoto');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;

        }

        private function SendVideo() {

            $BOT_TOKEN  = $this->parent->token; //----YOUR BOT TOKEN
            $chat_id    =   $this->channel_id; // or '123456' ------Receiver chat id

            define('BOTAPI', 'https://api.telegram.org/bot' . $BOT_TOKEN . '/');

            $cfile = new CURLFile(realpath($this->parent->file_path), 'video/mp4', $this->parent->file_path); //first parameter is YOUR IMAGE path

            $data = [
                'chat_id' => $chat_id,
                'video' => $cfile,
                'caption' => $this->parent->caption
            ];

            $ch = curl_init(BOTAPI . 'sendVideo');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;

        }

        private function SendAudio() {

            $BOT_TOKEN  = $this->parent->token; //----YOUR BOT TOKEN
            $chat_id    =   $this->channel_id; // or '123456' ------Receiver chat id

            define('BOTAPI', 'https://api.telegram.org/bot' . $BOT_TOKEN . '/');

            $cfile = new CURLFile(realpath($this->parent->file_path), 'audio/mpeg', $this->parent->file_path); //first parameter is YOUR IMAGE path

            $data = [
                'chat_id' => $chat_id,
                'audio' => $cfile,
                'caption' => $this->parent->caption
            ];

            $ch = curl_init(BOTAPI . 'sendAudio');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;

        }

    }