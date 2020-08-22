<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Payment_zarinpal extends CI_Driver {

        private $ci;
        private $parent;
		private $is_gate = FALSE;

        function __construct() {
            $this->ci       =   & get_instance();
            $this->ci->load->library('session');
            $this->parent   =  $this->ci->payment;
            include APPPATH.'libraries/Payment/drivers/nusoap.php';
        }

		function is_zarin_gate() {
			$this->is_gate	=	TRUE;
		}
		
        function pay($price) {

            if ($this->parent->is_rial)
                $price  =   $price / 10;

            $call_back  =   $this->parent->callback_url;
            if (strpos($call_back,'?') === FALSE)
                $call_back  .=  '?pay_type=zarinpal';
            else
                $call_back  .=  '&pay_type=zarinpal';

            if ($this->parent->payload != NULL)
                $call_back  .=  '&payload='.base64_encode(json_encode($this->parent->payload));

            $validation =   array('price' => $price,'timestamp' => time(),'mobile' => $this->parent->mobile);
            $validation['desc']     =   $this->parent->desc;
            $validation['email']    =   $this->parent->email;
            $validation['user_id']  =   $this->parent->user_id;
            $token  =   AUTHORIZATION::generateToken($validation);

            $call_back  .=  '&validation='.$token;

            libxml_disable_entity_loader(false);

            $parameter  =   [
                'MerchantID'  => $this->parent->merchant_id,
                'Amount'      => $price,
                'Description' => $this->parent->desc,
                'Email'       => $this->parent->email,
                'Mobile'      => $this->parent->mobile,
                'CallbackURL' => $call_back,
            ];

            $jsonData = json_encode($parameter);
            $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json');
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ));
            $result = curl_exec($ch);
            $err = curl_error($ch);
            $result = json_decode($result, true);
            curl_close($ch);

            if ($err) {
                return FALSE;
            } else {

                if ($result["Status"] == 100) {

                    $link	=	'https://www.zarinpal.com/pg/StartPay/'.$result["Authority"];

                    if ($this->is_gate)
                        $link	.=	'/ZarinGate';

                    header('Location: '.$link);

                } else {
                    return FALSE;
                }
            }

        }

        function verify() {

            if (!$this->ci->input->get('validation'))
                return FALSE;

            $this->ci->config->set_item('token_timeout',10);
            $validation     =   AUTHORIZATION::validateTimestamp($this->ci->input->get('validation'));

            if (!$validation)
                return FALSE;

            $validation     =   (array) $validation;
            $Authority      =   @$_GET['Authority'];
            $Price          =   $validation['price'];

            if (@$_GET['Status'] == 'OK') {

                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

                $result = $client->PaymentVerification(
                    [
                        'MerchantID'    => $this->parent->merchant_id,
                        'Authority'     => $Authority,
                        'Amount'        => $validation['price'],
                    ]
                );

                if ($result->Status == 100) {

                    if ($this->parent->is_rial)
                        $this->parent->set_var('price',$Price * 10);
                    else
                        $this->parent->set_var('price',$Price);

                    $this->parent->set_var('mobile',$validation['mobile']);
                    $this->parent->set_var('desc',$validation['desc']);
                    $this->parent->set_var('email',$validation['email']);
                    $this->parent->set_var('user_id',$validation['user_id']);
                    $this->parent->set_var('order_id',$result->RefID);

                    if (isset($_GET['payload'])) {
                        $this->parent->set_var('payload',json_decode(base64_decode($_GET['payload']),TRUE));
                    }

                    if ($this->parent->auto_save == TRUE)
                        $this->parent->save_transaction($this->parent->user_id,$result->RefID,$Price,$validation['desc']);

                    return TRUE;

                } else {

                    if (isset($_GET['payload'])) {
                        $this->parent->set_var('payload',json_decode(base64_decode($_GET['payload']),TRUE));
                    }

                    return FALSE;
                }

            } else {

                if (isset($_GET['payload'])) {
                    $this->parent->set_var('payload',json_decode(base64_decode($_GET['payload']),TRUE));
                }

                return FALSE;

            }

        }

    }