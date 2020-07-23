<?php if (! defined('BASEPATH') ) exit("Not Allow to access this Page");
 
    class Aes
    {
        
        var $bit    = 128;
        var $method = '';
        
        function __construct() {
            $this->setBit(256);
			$this->method   =    "aes-{$this->bit}-cbc";
        }

        function setBit($bit) {
            $this->method   =    "aes-$bit-cbc";
            $this->bit      =    $bit;
        }

        function encrypt($data,$key) {
			return $this->EncryptAES($key,$data));
        }

        function decrypt($data,$key) {
			return $this->DecryptAES($data,$key);
        }

		private function EncryptAES($message, $key){
			$ivsize = openssl_cipher_iv_length($this->method);
			$iv = openssl_random_pseudo_bytes($ivsize);
			$ciphertext = openssl_encrypt($message,$this->method,$key,OPENSSL_RAW_DATA,$iv);

			return $iv . $ciphertext;
		}
		  
		private function DecryptAES($message, $key){
			$message = base64_decode($message);
			$ivsize = openssl_cipher_iv_length($this->method);
			$iv = substr($message, 0, $ivsize);
			$ciphertext = substr($message, $ivsize);

			return openssl_decrypt($ciphertext,$this->method,$key,OPENSSL_RAW_DATA,$iv);
		}
	  
    }
   
?>
