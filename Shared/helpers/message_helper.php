<?php
defined('BASEPATH') OR exit('No direct script access allowed');

	function send_email($to,$subject,$body) {
		
		$me = & get_instance();
		
		$me->load->library('mailer');
		$me->mailer->send_mail($to,$subject,$body);
		
	}
	
	function send_email_message($to,$name,$body) {
		
		if (filter_var($to,FILTER_VALIDATE_EMAIL) == FALSE) return;
		
		$me = & get_instance();
		
		$app_title		=	$me->config->item('app_title');
		$template_email	=	$me->load->view('template/new_message',NULL,TRUE);
		$template_email	=	str_replace('{name}',$name,$template_email);
		$template_email	=	str_replace('{title}',$app_title,$template_email);
		$template_email	=	str_replace('{body}',nl2br($body),$template_email);
		
		$me->load->library('mailer');
		$me->mailer->send_mail($to,"پیام جدید از $app_title",$template_email);
		
	}
	
	function send_push($token,$data) {
		
		$me = & get_instance();
		
		$api	=	$me->config->item('api_push');
		$data	= array('to' => '/topics/'.$token , 'content_available'	 => false , 'data' => $data);

		ob_start();
		$curl	=	curl_init('https://fcm.googleapis.com/fcm/send');
		curl_setopt($curl, CURLOPT_POST,1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,FALSE);
		curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-type:application/json;charset=UTF-8','Authorization: key='.$api));
		curl_exec($curl);
		curl_close($curl);
		ob_clean();
		
	}
	
	function send_event_to_user($username,$subject,$message) {
		
		$me = & get_instance();
		
		$me->db->where('sMobile',$username);
		$res = $me->db->get('user');
		if ($res->num_rows() == 0) return FALSE;
		
		$user = $res->row();
		
		$app_title		=	$me->config->item('app_title');
		
		if (isset($user->sEmail))
			send_email_message($user->sEmail,"مدیریت $app_title",'شما پیام جدیدی دریافت کرده اید<br>لطفا وارد حساب کاربری خود شده و پیام را بررسی کنید');
		
		send_sms($username,"پیام جدید از مدیریت $app_title دریافت کرده اید\r\nوارد حساب خود شده و بررسی کنید");
		send_push($user->sDeviceID,array('title' => 'توجه','body' => $subject,'type' => 'message'));
		
	}
	
	function send_sms($to,$body,$is_flash=false,$is_array=false) {
		
		$me = & get_instance();
		
		if(preg_match("/^0[0-9]{10}$/",$to) || is_array($to)) {
			
			$webServiceURL  = "http://login.parsgreen.com/Api/SendSMS.asmx?WSDL";  
			$webServiceSignature = "E9D24ED6-24B4-4900-9ED3-5A37AAE0A4F6";
			$webServiceNumber   = "5000290";
			
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
				
				$responseSTD['retStr'] = @(array) $responseSTD['retStr'];

				 if (@$responseSTD['success']==1)
				 {
				   return $responseSTD['retStr']['string'];
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