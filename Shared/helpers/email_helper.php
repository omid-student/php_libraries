<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    function send_email($to,$subject,$body) {
		
		$me->load->library('mailer');
		$me->mailer->send_mail($to,$subject,$body);

    }