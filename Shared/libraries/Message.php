<?php if (! defined('BASEPATH') ) exit("Not Allow to access this Page");

 class Message {
		
		var $CI;
		var $push_api;
		var $reply_email;
		var $from_email;
		var $from_title;
		
		function __construct() {
			$this->CI	=	& get_instance();
		}
		
		//data contain title,body fields
		function sendPushTopic($data,$topic) {
			$this->SendPush($data,$topic,FALSE);
		}
		
		//data contain title,body fields
		//$id parameter must be array
		function sendPushUser($data,$id,$is_token	=	FALSE) {
			
			$api	=	$this->push_api;
			
			if (is_token == TRUE) {
				$registrationIds = $id;
				$fields = array
				(
					'registration_ids' 	=> $registrationIds,
					'data'				=> $data
				);
				
			} else {
				$fields = array
				(
					'to' 				=> "/topics/$id",
					'data'				=> $data
				);
			}
			
			$headers = array
			(
				'Authorization: key=' . $api,
				'Content-Type: application/json'
			);
			 
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
			curl_close( $ch );
			return $result;
			
		}
		 
		function sendEmail($to,$subject,$body) {

			$header  = "MIME-Version: 1.0\r\n";
			$header .= "Content-type: text/html; charset: utf-8\r\n";
			$header .= "Reply-To: ".$this->reply_email." <".$this->from_title.">\r\n";
			$header .= "Return-Path: Administrator <noreply@onestagram.ir>\r\n";
			$header .= "Organization: Administrator\r\n";
			$header .= "X-Priority: 3\r\n";
			$header .= "X-Mailer: PHP". phpversion() ."\r\n" ;
			$header .= "From: ".$this->from_title." <".$this->from_email.">\r\n";
			mail($to,$subject,$body,$header);

		}
		 
		function SendSMS($to,$body) {
			
			if(preg_match("/^0[0-9]{10}$/",$to) || is_array($to)) {}
			
		}
		
		function sendEmailMessage($to,$subject,$body) {
		
			if (filter_var($to,FILTER_VALIDATE_EMAIL) == FALSE) return;
			
			$template_email	=	$this->CI->load->view('template/new_message',NULL,TRUE);
			$template_email	=	str_replace('{title}',$subject,$template_email);
			$template_email	=	str_replace('{body}',nl2br($body),$template_email);
			
			$this->sendEmail($to,$subject,$template_email);
				
		}
	
}