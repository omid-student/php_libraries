<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Sms_parsgreen extends CI_Driver {

        private $ci;
        private $parent;

        function __construct() {
            $this->ci       =   & get_instance();
            $this->parent   =   $this->ci->sms;
        }

        function send() {

            $from_number    =   $this->parent->sender;
            $token          =   $this->parent->token;

            $body           =   urlencode($this->parent->text);
            $url            =   "https://login.parsgreen.com/UrlService/sendSMS.ashx?from=$from_number&to={$this->parent->mobile}&text=$body&signature=$token";
            $result         =   @file_get_contents($url);

            if (preg_match('/^\d+;\d+;\d+$/',$result))
                return TRUE;
            else
                return FALSE;

        }

        function credit() {

            $webServiceSignature    =   $this->parent->token;
            $webServiceURL          =   "http://login.parsgreen.com/Api/ProfileService.asmx?WSDL";

            $parameters = array(
                'signature' => $webServiceSignature,
            );

            try {

                $connectionS = new SoapClient($webServiceURL);
                $responseSTD = (array)$connectionS->GetCredit($parameters);
                return $responseSTD['GetCreditResult'];

            } catch (SoapFault $ex) {
                return FALSE;
            }

        }

    }