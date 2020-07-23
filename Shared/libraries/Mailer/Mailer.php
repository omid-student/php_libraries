<?php if (! defined('BASEPATH') ) exit("Not Allow to access this Page");

 class Mailer
  {
		function send($to,$to_name,$subject,$body) {

			require_once APPPATH."libraries/phpmailer/class.phpmailer.php";
			require_once APPPATH."libraries/phpmailer/class.smtp.php";

			$mail= new PHPMailer();
			$mail->IsSMTP();
			$mail->SMTPAuth=true;
			$mail->Host       = 'localhost';
			$mail->Username   = 'support@behdidco.com';
			$mail->Password   = 'QKUj=dESQ4bq';
			$mail->Port       = 25;
			$mail->IsHTML(true);

			$mail->AddAddress($to,$to_name);
			$mail->SetFrom('support@behdidco.com','مدیریت به دید');
			$mail->Subject    = $subject;
			$mail->MsgHTML($body);

			@$mail->Send();
			
	 }
	
}