<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Sms_smsir extends CI_Driver {

        private $ci;
        private $parent;
        private $parameters = [];
        private $template_id;

        function __construct() {
            $this->ci       =	& get_instance();
            $this->parent   =	$this->ci->sms;
        }

        function add_parameter($parameter_name,$value) {
            $this->parameters[]  =   array('Parameter' => $parameter_name,'ParameterValue' => $value);
            return $this;
        }

        function set_template_id($template_id) {
            $this->template_id = $template_id;
            return $this;
        }

        function send() {

            if (count($this->parameters) == 0) {
                $result =   $this->send_simple();
                return $result;
            }

            $token  =   $this->get_token_sms_ir();

            $template   =   json_encode($this->parameters);
            $fields     =   "{\r\n \"ParameterArray\":{$template},\r\n\"Mobile\":\"{$this->parent->mobile}\",\r\n\"TemplateId\":\"{$this->template_id}\"\r\n}";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://RestfulSms.com/api/UltraFastSend",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-sms-ir-secure-token: $token"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return FALSE;
            } else {

                $res = json_decode($response);
                if ($res == FALSE)
                    return FALSE;

                if ($res->IsSuccessful == FALSE)
                    return FALSE;
                else
                    return TRUE;
            }

        }

        function credit() {

            $token  =   $this->get_token_sms_ir();

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://RestfulSms.com/api/credit",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-sms-ir-secure-token: $token"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            if (json_decode($response)) {
                return json_decode($response)->Credit;
            } else {
                return FALSE;
            }

        }

        private function get_token_sms_ir() {

            $api_key    =   $this->parent->token;
            $secret_key =   $this->parent->secret_key;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://ws.sms.ir/api/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\n\t\"UserApiKey\":\"$api_key\",\n\t\"SecretKey\":\"$secret_key\"\n}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return "";
            } else {
                $response = json_decode($response);
                if (is_object($response)) {
                    if ($response->IsSuccessful == TRUE)
                        return $response->TokenKey;
                    else
                        return "";
                } else {
                    return "";
                }
            }

        }

        private function send_simple() {

            $token  =   $this->get_token_sms_ir();

            $data                   =   [];
            $data['Messages']       =   array($this->parent->text);
            $data['MobileNumbers']  =   array($this->parent->mobile);
            $data['LineNumber']     =   $this->parent->sender;
            $data['SendDateTime']   =   "";
            $data['CanContinueInCaseOfError']   =   "";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://RestfulSms.com/api/MessageSend",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "Accept: */*",
                    "Accept-Encoding: gzip, deflate",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "Content-Type: application/json",
                    "Host: restfulsms.com",
                    "cache-control: no-cache",
                    "x-sms-ir-secure-token: $token"
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $response   =   json_decode($response);

            if ($response == FALSE)
                return FALSE;
            else {
                if ($response->IsSuccessful == FALSE)
                    return FALSE;
                else
                    return TRUE;
            }

        }

    }