<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 

class Rsa2 extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library('RSA');
	}
	
	public function index()
	{
		$arr = $this->rsa->create_public_key(1024);
		$arr['privatekey'] = base64_encode($arr['privatekey']);
		echo json_encode($arr);
	}
	
	function encrypt() {
		
	}
	
	public function decrypt() {
		echo $this->rsa->decrypt('-----BEGIN PRIVATE KEY-----LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KTUlJQ1d3SUJBQUtCZ1FDSzk2T3JPZTVlaTJhMjdRYnQ1Rm9hRmQ2d3NxRUxyRGNwalVMS3JaaFZ1YThWMU1SVg0KZStGWWZqMUdzSFQ2MUpSbzAyTHE1VlByTG5XOGhub1NxSnU2S3ZMaXV0WWJJMDdTMTdjVlpobCtTNFpjRVlOTg0KbU1xNTFWaTl3SjkvNXlEaG9mb2JMeXhNbExjakJieDlLUTEvbFI0R25KQ01qZVZndXE5S2FBRm9Qd0lEQVFBQg0KQW9HQUNtVWwxV2JGb1pyZk9jQnNscHBRbkhKQVd6VmNLSjhKbC9rY3paa1lzNzQvdU93QkxSSjhNOU5xOVZaag0KeEdqbkdtN3k1UGFoOEE0UFRvL25UUmdCdGdRZUw5QStPeUt5OTZtWC9RWEdWQlR6OG9qS2Jlay9mSlZXTEhiUQ0KREVpczNOMU45L1hSaVBUUk1QY3A0TDhmOWNPTFk0WGRqQjVVNVZpbmU2UTBOWmtDUVFDWGFTRXBZK0xlQ2hTUQ0KcEJPekdlYkQwV203SHM5b2JrWk1wWW5yY05sTEZnVHRqN3hrQUkzaW9QditYdVo1aFArc2Z4YVNOWm5hZVowTg0KOFdzZFgwT2RBa0VBNnZZVTJxT1F2Q2FYNnpoVVJYMzR6U1A1elJ1azVwc045VFZoU0VRT1ZUM250dVZlbEJaeg0KYlo1TkttblVpQTJDaHpTQ2ZLbEhhSGVReEM0VDRFVGFpd0pBZDJEVVhLWDh3Y1NBNG1uN2ZrTDF4MzdkTmtQYw0KbENJZUcyQ0psYkNzSXArQjB5eDVCUC9LM3R5KzBwaFFiNCtGWnFQdFQrb2pIRGEydGIrYkROV0hvUUpBTGNkdQ0KZnI3NXR6OEp3SUhFSVpvT3ZPRnlqVjBDZncvYzQxYllNbjFZRVpHek1QWjF2QUszMExiVU1CeFlsWDJVWGdXRQ0KR3dmY2F1Vkk1b0JYelExOVN3SkFlalQ4OEU1a0c5emFMY3ZLWWI2dGpDNmpkZWF0bCs2WEIyQi9RK0NZa21VMg0KQmQ4NkdDKzBaWGUyU2RpeWxkMHUzMXp2NXZxNkE3K1Eyc1pJTnZkWnpnPT0NCi0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0t-----END PRIVATE KEY-----','TdsDPqdywxw/NaoF6anqVhcFXPspsHczMw+hjsyKi25aIOoFQTgdjiwjRCRsPieVUmzkmQRgS0gQfpTEcXRJRFNlb5UxciIsC1FoG/fHYzapdWxvu5UJ/FJvKTyAUNQVtpABrW44KPE5olc/iRYdiVUzfcuUqmPsarSRUV3S09s=');
	}
	
}
