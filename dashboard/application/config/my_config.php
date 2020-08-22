<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['sms_limit']                =   10;
$config['timeout_confirm_code']     =   885; //per second
$config['timeout_token']            =   864000; //per second for user token

//مدت زمان بقای کد فعالسازی
$config['confirmation_code_alive']  =   58;
$config['register_code_alive']      =   159;

$config['project_title']            =   'پروژه شما';

$config['per_page']                 =   20;

defined('SMS_CONFIRMATION_CODE') or define('SMS_CONFIRMATION_CODE',23098);
defined('SMS_USER_CONFIRMATION_CODE') or define('SMS_USER_CONFIRMATION_CODE',24913);
defined('SMS_REGISTER_COMPLETE_CODE') or define('SMS_REGISTER_COMPLETE_CODE',23100);

$config['smsir_apikey']	            =	'17720f3c5bd6698bed8c0f3c';
$config['smsir_secretkey']	        =	'ygjhgasdadaFSDASDsd7';