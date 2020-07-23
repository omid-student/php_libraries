<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    function send_sms($to,$body,$is_flash = FALSE) {
 
        libxml_disable_entity_loader(false);

        if(preg_match("/^0[0-9]{10}$/",$to) || is_array($to))
        {
            $webServiceURL          = "http://login.parsgreen.com/Api/SendSMS.asmx?WSDL";
            $webServiceSignature    = "CC75FD87-B4BE-4FEF-8A02-C3EA79968DF2";

            $webServiceNumber       = "10001398";

            if (!is_array($to)) {
                $Mobiles      = array ($to);
            }
            else {
                $Mobiles	  = $to;
            }

            $isFlash = $is_flash;

            mb_internal_encoding("utf-8");
            $textMessage=$body;
            $textMessage= mb_convert_encoding($textMessage,"UTF-8");

            $parameters['signature']	= $webServiceSignature;
            $parameters['from'] 		= $webServiceNumber;
            $parameters['to']			= $Mobiles;
            $parameters['text']			= $textMessage;
            $parameters['isFlash']		= $isFlash;
            $parameters['udh']			= "";
            $parameters['success']		= 0x0;
            $parameters['retStr']		= array( 0  );

            try
            {
                $con = new SoapClient($webServiceURL);

                $responseSTD = (array) $con->SendGroupSMSSimple($parameters);

                $responseSTD['retStr'] = (array) @$responseSTD['retStr'];

                if ( @$responseSTD['success']==1)
                {
                    return @$responseSTD['retStr']['string'];
                }
                else
                {
                    return $responseSTD;
                }
            }
            catch (SoapFault $ex)
            {

            }
        }

    }