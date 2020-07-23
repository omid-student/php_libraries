<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
			
	class RSA {
		
		var $RSA2;
		
		function __construct() {
			require_once str_replace("\\","/",APPPATH).'libraries/Crypt/RSA.php';
			$this->RSA2 = new Crypt_RSA();
			$this->RSA2->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		}
		
		function create_public_key($size = 1024) {
			return $this->RSA2->createKey($size);
		}
		
		function encrypt($publickey,$data) {
			$this->RSA2->loadKey($publickey);
			$ciphertext = $this->RSA2->encrypt($data);
			return base64_encode($ciphertext);
		}
		
		function decrypt($privatekey,$data) {
			$this->RSA2->loadKey($privatekey);
			$ciphertext = $this->RSA2->decrypt(base64_decode($data));
			return $ciphertext;
		}
		
	}
?>