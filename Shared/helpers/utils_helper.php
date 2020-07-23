<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    //<editor-fold desc="Validation data">
	function validate_time($time) {
	    return preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time);
    }

    function validate_color($color) {
	    return preg_match('/#([a-f0-9]{3}){1,2}\b/i',$color);
    }

    function validate_boolean($data) {
        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
    }

	function validate_string($data,$min_length = 3,$latin = FALSE) {

	    $data   =   trim($data);

        if (strlen($data) < $min_length)
            return FALSE;
        else
            return TRUE;

	}
	
	function validate_username($data) {
		return preg_match('/^[0-9a-zA-Z\.]+$/', $data);
	}
	
	function validate_email($data) {
		return filter_var($data,FILTER_VALIDATE_EMAIL);
	}

    function validate_url($data) {
        return filter_var($data,FILTER_VALIDATE_URL);
    }

    function validate_range($data,$from,$to) {

	    if (!validate_number($data))
	        return FALSE;

	    if ($data >= $from && $data <= $to)
	        return TRUE;

	    return FALSE;

    }

	function validate_int($data) {
		return filter_var($data,FILTER_VALIDATE_INT);
	}

	function validate_float($data) {
	    return filter_var($data,FILTER_VALIDATE_FLOAT);
    }
	
	function validate_number($data) {
		return validate_int($data);
	}

	//check characters is number only
	function validate_numbers($data) {
	    return preg_match('/\d+/',$data);
    }

	//at least password is 5 character
	function validate_password($data) {
		return preg_match('/^[\S|\s]{5,}$/',$data);
	}

	function validate_phone($phone) {
		  return preg_match("/^[0-9]{8,15}$/",$phone);
	}

    function validate_phone_ir($phone) {
        return preg_match("/^0[0-9]{10}$/",$phone);
    }
	
	function valid_phone($phone) {
		  return validate_phone($phone);   
	}
	
	function validate_date($date,$separator ='/') {
		return preg_match("|^\d{4}$separator\d{1,2}$separator\d{1,2}$|",$date);
	}

    function validate_location($location,$output = FALSE) {

	    if ($location == '') return FALSE;

        if (!preg_match("/^(((-?)\d+\.\d+)|(-?)\d+),(((-?)\d+\.\d+)|(-?)\d+)/",str_replace(' ','',$location)))
            return FALSE;
        else {
            if ($output == FALSE) return TRUE;
            return explode(',',$location);
        }
    }

    function validate_json_array($data) {

	    if (json_decode($data) == FALSE)
	        return FALSE;

	    $res    =   json_decode($data);
	    if (is_array($res))
	        return TRUE;
	    else
	        return FALSE;

    }

    function validate_json_object($data) {

        if (json_decode($data) == FALSE)
            return FALSE;

        $res    =   json_decode($data);
        if (!is_array($res))
            return TRUE;
        else
            return FALSE;

    }

	function validate_audio($path) {
			
		$info = new finfo(FILEINFO_MIME);
		$type = $info->buffer(file_get_contents($path));

		switch ($type) {
			case 'audio/mpeg':
				return TRUE;
				break;
			case 'audio/ogg':
				return TRUE;
				break;
			case 'audio/wav':
				return TRUE;
				break;
			case 'audio/x-matroska':
				return TRUE;
				break;
			case 'audio/mp4':
				return TRUE;
				break;
			default:
				return FALSE;
				break;
		}
		
		return FALSE;

	}

    function validate_picture($path) {
        if ($path == '') return FALSE;
        if (file_exists($path) == FALSE) return FALSE;
        $is_picture	= getimagesize($path);
        if ($is_picture === FALSE) return FALSE;
        return TRUE;
    }

    function validate_picture_size($path,$width,$height,$op) {

        $is_picture	= getimagesize($path);
        if ($is_picture === FALSE) return FALSE;

        if ($op	==	'>') {
            if ($is_picture[0] > $width && $is_picture[1] > $height)
                return TRUE;
            else
                return FALSE;
        } else if ($op	==	'<') {
            if ($is_picture[0] < $width && $is_picture[1] < $height)
                return TRUE;
            else
                return FALSE;
        } else if ($op	==	'=') {
            if ($is_picture[0] == $width && $is_picture[1] == $height)
                return TRUE;
            else
                return FALSE;
        }

    }

    function validate_picture_type($path,$type) {

        $picture_type	=	exif_imagetype($path);

        switch(strtolower($type)) {
            case "gif":
                if ($picture_type	!=	IMAGETYPE_GIF) return FALSE;
                break;

            case "jpg":
            case "jpeg":
                if ($picture_type	!=	IMAGETYPE_JPEG) return FALSE;
                break;

            case "png":
                if ($picture_type	!=	IMAGETYPE_PNG) return FALSE;
                break;
        }

        return TRUE;

    }

    function validate_video($filename) {

        $extension	=	get_mime($filename);

        if(strpos($extension,'mp4') !== FALSE)
            return TRUE;
        else
            return FALSE;

    }

    function validate_upload_filesize($path,$size) {

        if (filesize($path) > $size)
            return FALSE;
        else
            return TRUE;

    }

    function validate_upload($key_name,$is_image = TRUE) {

        if (empty($_FILES))											return FALSE;
        if (!isset($_FILES[$key_name]))								return FALSE;
        if ($_FILES[$key_name]['error']	!=	0)						return FALSE;
        if ($_FILES[$key_name]['size']	==	0)						return FALSE;
        if (!is_uploaded_file($_FILES[$key_name]['tmp_name']))		return FALSE;
        if (!file_exists($_FILES[$key_name]['tmp_name']))			return FALSE;

        if ($is_image	==	TRUE) {

            $file_type	=	$_FILES[$key_name]['type'];

            //if (strpos($file_type,'image')	===	FALSE) 				return FALSE;
            if (!validate_picture($_FILES[$key_name]['tmp_name']))	return FALSE;

            return TRUE;

        } else {
            return TRUE;
        }

    }

    function validate_zip($filename) {

        if(is_resource($zip = zip_open($filename))) {
            zip_close($zip);
            return TRUE;
        }
        else {
            return FALSE;
        }

    }

    function validate_base64_picture($base64) {

    $img = @imagecreatefromstring(base64_decode($base64));
    if (!$img) {
        return false;
    }

    imagepng($img, 'tmp.png');
    $info = getimagesize('tmp.png');

    $file_size  =   @filesize('tmp.png');

    unlink('tmp.png');

    if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
        $info['size']   =   $file_size;
        return $info;
    }

    return false;

}

    function validate_imei($imei){

    // Should be 15 digits
    if(strlen($imei) != 15 || !ctype_digit($imei))
        return false;
    // Get digits
    $digits = str_split($imei);
    // Remove last digit, and store it
    $imei_last = array_pop($digits);
    // Create log
    $log = array();
    // Loop through digits
    foreach($digits as $key => $n){
        // If key is odd, then count is even
        if($key & 1){
            // Get double digits
            $double = str_split($n * 2);
            // Sum double digits
            $n = array_sum($double);
        }
        // Append log
        $log[] = $n;
    }
    // Sum log & multiply by 9
    $sum = array_sum($log) * 9;
    // Compare the last digit with $imei_last
    return substr($sum, -1) == $imei_last;

}
    //</editor-fold>

    //<editor-fold desc="Generation data">
    function random_password($length = 8) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function random_token($length = 64) {
        $token = bin2hex(openssl_random_pseudo_bytes($length));
        return $token;
    }

    function generate_ticket_id($user_id) {
    $cur_date = date('dmyHis');
    $ticket = '#'.$user_id.'-'. $cur_date;
    return $ticket;
}

    function generate_code(){

    $unique =   FALSE;
    $length =   7;
    $chrDb  =   array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

    while (!$unique){

        $str = '';
        for ($count = 0; $count < $length; $count++){

            $chr = $chrDb[rand(0,count($chrDb)-1)];

            if (rand(0,1) == 0){
                $chr = strtolower($chr);
            }
            if (3 == $count){
                $str .= '-';
            }
            $str .= $chr;
        }

        /* check if unique */
        $existingCode = null;
        if (!$existingCode){
            $unique = TRUE;
        }
    }
    return $str;
}

    function generate_uuid() {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    function generate_token() {
        $tokenR = md5(uniqid(rand(), TRUE));
        return $tokenR;
    }

    function generate_random_number($length = 15, $formatted = FALSE) {
    $nums = '0123456789';

    // First number shouldn't be zero
    $out = $nums[mt_rand( 1, strlen($nums)-1 )];

    // Add random numbers to your string
    for ($p = 0; $p < $length-1; $p++)
        $out .= $nums[mt_rand( 0, strlen($nums)-1 )];

    // Format the output with commas if needed, otherwise plain output
    if ($formatted)
        return number_format($out);
    return $out;
}
    //</editor-fold>

    //<editor-fold desc="Datetime section">
    function get_current_date($format = "Y/n/j") {
        require_once APPPATH.'helpers/jdf.php';
        return jdate($format,date(time()));
    }

    function get_persian_date($time,$short = TRUE) {
        require_once APPPATH.'helpers/jdf.php';
        if ($short)
            return jdate("Y/n/j",date(strtotime($time)));
        else
            return jdate("j F Y",date(strtotime($time)));
    }

    function get_english_date($year,$month,$day) {
        require_once APPPATH.'helpers/jdf.php';
        return jalali_to_gregorian($year,$month,$day);
    }

    function get_date($time,$split=false) {

        require_once APPPATH.'helpers/jdf.php';

        if ($split == FALSE)
            return jdate("Y/n/j",date(strtotime($time)));
        else {
            $date	=	date(strtotime($time));
            return array(jdate("Y",$date),jdate("n",$date),jdate("j",$date));
        }

    }

    function get_name_date($time,$splite = FALSE) {

        require_once APPPATH.'helpers/jdf.php';
        $res = jdate("j F Y",date(strtotime($time)));

        if ($splite == FALSE)
            return $res;
        else
            return explode(' ',$res);

    }

    function get_name_datetime($time,$splite = FALSE) {

    require_once APPPATH.'helpers/jdf.php';
    $res = jdate("j F Y H:i:s",date(strtotime($time)));

    if ($splite == FALSE)
        return $res;
    else
        return explode(' ',$res);

}

    function timestamp($unix = false) {
    date_default_timezone_set('UTC');
    if ($unix) {
        $d = new DateTime();
        return $d->getTimestamp();
    } else {
        return date('Y-m-d H:i:s');
    }
}

    /**
     * Get different two datetime in format yyyy-mm-dd
     * @param $from format is yyyy-mm-dd
     * @param $to   format is yyyy-mm-dd
     * @return string is day
     */
    function get_different_date($from,$to) {
        $date1=date_create($from);
        $date2=date_create($to);
        $diff=date_diff($date1,$date2);
        return $diff->format("%R%a");
    }

    /***
     * Get different two datetime in format yyyy-mm-dd hh:ii:ss
     * @param $from
     * @param $to
     * @return array
     */
    function get_different_datetime($from,$to) {

    $datetime1 = new DateTime($from);
    $datetime2 = new DateTime($to);
    $interval = $datetime1->diff($datetime2);

    $res = array();
    $res['year']    =   $interval->format('%y');
    $res['month']   =   $interval->format('%m');
    $res['day']     =   $interval->format('%a');
    $res['hour']    =   $interval->format('%h');
    $res['minute']  =   $interval->format('%i');
    $res['second']  =   $interval->format('%s');

    return $res;

}

    /**
     * @param $date
     * @param $interval +1 days , -2 hours , +1 second , +1 week , last Sunday
     * @return string
     */
    function change_date_interval($date,$interval) {
    return date('Y-m-d',strtotime($date." $interval"));
}

    /**
     * Use H for hour format in datetime
     * @param $last_date your datetime
     * @return string
     */
    function time_ago($last_date) {

    if (preg_match('/^\d+$/',$last_date)) {
        $last_date	=	$last_date;
    }
    else
        $last_date	=	strtotime($last_date);

    $now = time();
    $time = $last_date;
    // catch error
    if (!$time) {
        return $last_date;
    }
    // build period and length arrays
    $periods = array('ثانیه', 'دقیقه', 'ساعت', 'روز', 'هفته', 'ماه', 'سال', 'قرن');
    $lengths = array(60, 60, 24, 7, 4.35, 12, 10);
    // get difference
    $difference = $now - $time;
    // set descriptor
    if ($difference < 0) {
        $difference = abs($difference); // absolute value
        $negative = true;
    }
    // do math
    for ($j = 0; $difference >= $lengths[$j] and $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    // round difference
    $difference = intval(round($difference));

    if ($difference == 0) return 'لحظاتی پیش';

    // return
    return number_format($difference) . ' ' . $periods[$j] . ' ' . (isset($negative) ? '' : 'پیش');

}
    //</editor-fold>

	function is_ajax_request() {
		if (strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest')
			return TRUE;
		else
			return FALSE;
	}
	
	function get_header_key($keys,$array = false) {
		
		$headers = array();
		
		foreach($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_') {
				continue;
			}
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;
		}
		
		if ($array == true) return $headers;
		
		foreach ($headers as $header => $value) {
			if (strtolower($header) == strtolower($keys)) return $value;
		}
		
	}

	function base64_to_picture($base64_string, $output_file) {
		
		$res = str_replace(' ', '+', $base64_string);
		$ifp = fopen($output_file, "wb"); 
		fwrite($ifp, base64_decode($res)); 
		fclose($ifp); 

		return $output_file; 
	}
	
	function extract_email($text) {
		$res = preg_match_all("/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i", $text,$match);
		return $match;
	}

    function create_thumbnail($image_name,$image_dir,$new_width,$new_height,$output_dir)
    {

        $path = $image_dir . '/' . $image_name;

        $mime = getimagesize($path);

        if($mime['mime']=='image/png') {
            $src_img = imagecreatefrompng($path);
        }
        if($mime['mime']=='image/jpg' || $mime['mime']=='image/jpeg' || $mime['mime']=='image/pjpeg') {
            $src_img = imagecreatefromjpeg($path);
        }

        $old_x          =   imageSX($src_img);
        $old_y          =   imageSY($src_img);

        if($old_x > $old_y)
        {
            $thumb_w    =   $new_width;
            $thumb_h    =   $old_y*($new_height/$old_x);
        }

        if($old_x < $old_y)
        {
            $thumb_w    =   $old_x*($new_width/$old_y);
            $thumb_h    =   $new_height;
        }

        if($old_x == $old_y)
        {
            $thumb_w    =   $new_width;
            $thumb_h    =   $new_height;
        }

        $dst_img        =   ImageCreateTrueColor($thumb_w,$thumb_h);

        imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);


        // New save location
        $new_thumb_loc = $output_dir . $image_name;

        if($mime['mime']=='image/png') {
            $result = imagepng($dst_img,$new_thumb_loc,8);
        }
        if($mime['mime']=='image/jpg' || $mime['mime']=='image/jpeg' || $mime['mime']=='image/pjpeg') {
            $result = imagejpeg($dst_img,$new_thumb_loc,80);
        }

        imagedestroy($dst_img);
        imagedestroy($src_img);

        return $result;

    }

    function create_captcha2($image_path,$code = '',$colors = array()) {

		    $CI = & get_instance();
			$CI->load->helper('captcha');

			$data['captchaCode'] =  ($code == '' ? rand(1000,9999) : $code);

			$vals                =  array(
				'word'           => $data['captchaCode'],
				'img_path'       => $image_path,
				'img_url'        => $CI->config->base_url(),
				'font'           => '',
				'img_width'      => '100',
				'img_height'     => 36,
				'expiration'     => 295, // 2 minute
				'time'           => time(),
                'colors'         => (count($colors) > 0 ? $colors : array(
                    'background' => array(255, 255, 255),
                    'border'     => array(255, 255, 255),
                    'text'       => array(0, 0, 0),
                    'grid'       => array(255, 40, 40)
                ))
			);

			$data['imgCaptcha']  = create_captcha($vals);

			return array('code' => $data['captchaCode'],'image_name' => $data['imgCaptcha']['image'],'filename' => $data['imgCaptcha']['filename']);

	}
	
	function get_user_ip() {

		$ipaddress = '';

		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';

		if ($ipaddress == '::1' || $ipaddress == 'localhost')
		    $ipaddress  =   '151.235.98.154';

		if (strpos($ipaddress,',') !== FALSE) {
		    $ipaddress  =   explode($ipaddress)[0];
        }

		return $ipaddress;

	}
	
	function number_to_word($strnum) {

			if ($strnum == '0') return 'صفر';
			$strnum = trim($strnum);
			$size_e = strlen ( $strnum );
			
			for($i = 0; $i < $size_e; $i ++) {
				if (! ($strnum [$i] >= "0" && $strnum [$i] <= "9")) {
					die ( "content of string must be number. " . 'فقط عدد وارد کنید'.$strnum );
				
				}
			}
			
			for($i = 0; $i < $size_e && $strnum [$i] == "0"; $i ++)
				;
			
			$str = substr ( $strnum, $i );
			$size = strlen ( $str );
			
			$arr = array ();
			$res = "";
			$mod = $size % 3;
			if ($mod) {
				$arr [] = substr ( $str, 0, $mod );
			}
			
			for($j = $mod; $j < $size; $j += 3) {
				$arr [] = substr ( $str, $j, 3 );
			}
			
			$arr1 = array ("", "یک", "دو", "سه", "چهار", "پنج", "شش", "هفت", "هشت", "نه" );
			$arr2 = array (1 => "یازده", "دوازده", "سیزده", "چهارده", "پانزده", "شانزده", "هفده", "هجده", "نوزده" );
			$arr3 = array (1 => "ده", "بیست", "سی", "چهل", "پنجاه", "شصت", "هفتاد", "هشتاد", "نود" );
			$arr4 = array (1 => "صد", "دویست", "سیصد", "چهارصد", "پانصد", "ششصد", "هفتصد", "هشتصد", "نهصد" );
			$arr5 = array (1 => "هزار", "میلیون", "میلیارد", "تیلیارد" );
			$explode = 'و';
			$size_arr = count ( $arr );
			
			if ($size_arr > count ( $arr5 ) + 1) {
				die ( "عدد بسیار بزرگ است . " . 'this number is greate' );
			
			}
			
			for($i = 0; $i < $size_arr; $i ++) {
				
				$flag_2 = 0;
				$flag_1 = 0;
				
				if ($i) {
					$res .= ' ' . $explode . ' ';
				}
				
				$p = $arr [$i];
				$ss = strlen ( $p );
				
				for($k = 0; $k < $ss; $k ++) {
					if ($p [$k] != "0") {
						break;
					}
				}
				
				$p = substr ( $p, $k );
				$size_p = strlen ( $p );
				
				if ($size_p == 3) {
					$res .= $arr4 [( int ) $p [0]];
					$p = substr ( $p, 1 );
					$size_p = strlen ( $p );
					
					if ($p [0] == "0") {
						$p = substr ( $p, 1 );
						$size_p = strlen ( $p );
						if ($p [0] == "0") {
							continue;
						} else {
							$flag_1 = 1;
						}
					
					} else {
						$flag_2 = 1;
					}
				
				}
				
				if ($size_p == 2) {
					
					if ($flag_2) {
						$res .= ' ' . $explode . ' ';
					}
					
					if ($p >= "0" && $p <= "9") {
						$res .= $arr1 [( int ) $p];
					} elseif ($p >= "11" && $p <= "19") {
						$res .= $arr2 [( int ) $p [1]];
					} elseif ($p [0] >= "1" && $p [0] <= "9" && $p [1] == "0") {
						$res .= $arr3 [( int ) $p [0]];
					} else {
						$res .= $arr3 [( int ) $p [0]];
						$res .= ' ' . $explode . ' ';
						$res .= $arr1 [( int ) $p [1]];
					}
				
				}
				
				if ($size_p == 1) {
					
					if ($flag_1) {
						$res .= ' ' . $explode . ' ';
					}
					
					$res .= $arr1 [( int ) $p];
				
				}
				
				if ($i + 1 < $size_arr) {
					$res .= ' ' . $arr5 [$size_arr - $i - 1];
				}
			
			}
			
			$res = rtrim($res,' و');
				
			return $res.' تومان';
	}

	function get_file_extension($file) {
	    return pathinfo($file,PATHINFO_EXTENSION);
    }

	function get_current_url() {
		return current_url().'?'.$_SERVER['QUERY_STRING'];
	}
	
	function go_referer($redirect = FALSE) {
		if ($redirect == TRUE) {
			if (isset($_SERVER['HTTP_REFERER']))
				redirect($_SERVER['HTTP_REFERER']);
		}
		else {
			if (isset($_SERVER['HTTP_REFERER']))
				return $_SERVER['HTTP_REFERER'];
			else
				return current_url();
		}
	}
	
	function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") 
						 rrmdir($dir."/".$object); 
					else {
					    if ($dir."/".$object != 'files/shop')
					    unlink($dir."/".$object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	 }
	
	function delete_directory($dir) {

	    if (!is_dir($dir)) return;

		$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
		
		foreach($files as $file) {
			if ($file->isDir()){
			@rmdir(@$file->getRealPath());
			} else {
				@unlink($file->getRealPath());
			}
		}
		
		@rmdir($dir);
		
	 }
	
	function get_mime($file) {
		if (function_exists("finfo_file")) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		} else if (function_exists("mime_content_type")) {
			return mime_content_type($file);
		} else if (!stristr(ini_get("disable_functions"), "shell_exec")) {
			// http://stackoverflow.com/a/134930/1593459
			$file = escapeshellarg($file);
			$mime = shell_exec("file -bi " . $file);
			return $mime;
		} else {
			return false;
		}
	}
	
	function convert_numbers($srting,$toPersian=true)
	{
		$en_num = array('0','1','2','3','4','5','6','7','8','9');
		$fa_num = array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹');
		if( $toPersian ) return str_replace($en_num, $fa_num, $srting);
			else return str_replace($fa_num, $en_num, $srting);
	}
	
	function isValidTimeStamp($timestamp) {
	  return ((string) (int) $timestamp === $timestamp)
		&& ($timestamp <= PHP_INT_MAX)
		&& ($timestamp >= ~PHP_INT_MAX);
	}

    //truncate string to words and limit it
	function truncate_string($text, $limit) {
      if (str_word_count($text, 0) > $limit) {
          $words = str_word_count($text, 2);
          $pos = array_keys($words);
          $text = substr($text, 0, $pos[$limit]) . '...';
      }
      return $text;
    }

    //ellipsize string only
    function truncate_string2($string, $length)
    {
        if (strlen($string) > $length) {
            $string = substr($string, 0, $length) . '...';
        }

        return $string;
    }
	
	function referer_url($default = '') {
		$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if ($url == '')
			if ($default != '') $url = $default;
		return $url;
	}

    function force_download($path)
    {

        $name = basename($path);

        // make sure it's a file before doing anything!
        if (is_file($path)) {
            // required for IE
            if (ini_get('zlib.output_compression')) {
                ini_set('zlib.output_compression', 'Off');
            }

            // get the file mime type using the file extension
            $ci = & get_instance();
            $ci->load->helper('file');

            $mime = get_mime_by_extension($path);

            // Build the headers to push out the file properly.
            header('Pragma: public');     // required
            header('Expires: 0');         // no cache
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
            header('Cache-Control: private', false);
            header('Content-Type: ' . $mime);  // Add the mime type from Code igniter.
            header('Content-Disposition: attachment; filename="' . basename($name) . '"');  // Add the file name
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($path)); // provide file size
            header('Connection: close');
            readfile($path); // push it out
            exit();
        }

    }
	
	function force_download_resumable($path) {
		
		date_default_timezone_set('GMT');

		//1- file we want to serve : 
		$data_file = $path;

		$data_size = filesize($data_file); //Size is not zero base

		$mime = 'application/otect-stream'; //Mime type of file. to begin download its better to use this.
		$filename = basename($data_file); //Name of file, no path included

		//2- Check for request, is the client support this method?
		if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
			
			$ranges_str = (isset($_SERVER['HTTP_RANGE']))?$_SERVER['HTTP_RANGE']:HTTP_SERVER_VARS['HTTP_RANGE'];
			$ranges_arr = explode('-', substr($ranges_str, strlen('bytes=')));
			
			//Now its time to check the ranges
			if ((intval($ranges_arr[0]) >= intval($ranges_arr[1]) && $ranges_arr[1] != "" && $ranges_arr[0] != "" )
			|| ($ranges_arr[1] == "" && $ranges_arr[0] == "")
			) {
				//Just serve the file normally request is not valid :( 
				$ranges_arr[0] = 0;
				$ranges_arr[1] = $data_size - 1;
			}
		} else { //The client dose not request HTTP_RANGE so just use the entire file
			$ranges_arr[0] = 0;
			$ranges_arr[1] = $data_size - 1;
		}

		//Now its time to serve file 
		$file = fopen($data_file, 'rb');

		$start = $stop = 0;
		if ($ranges_arr[0] === "") { //No first range in array
			//Last n1 byte
			$stop = $data_size - 1;
			$start = $data_size - intval($ranges_arr[1]);
		} elseif ($ranges_arr[1] === "") { //No last
			//first n0 byte
			$start = intval($ranges_arr[0]);
			$stop = $data_size - 1;
		} else {
			// n0 to n1
			$stop = intval($ranges_arr[1]);
			$start = intval($ranges_arr[0]);
		}    
		//Make sure the range is correct by checking the file

		fseek($file, $start, SEEK_SET);
		$start = ftell($file);
		fseek($file, $stop, SEEK_SET);
		$stop = ftell($file);

		$data_len = $stop - $start;

		//Lets send headers 

		if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
			header('HTTP/1.0 206 Partial Content');
			header('Status: 206 Partial Content');
		}
		
		header('Accept-Ranges: bytes');
		header('Content-type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $filename . '"'); 
		header("Content-Range: bytes $start-$stop/" . $data_size );
		header("Content-Length: " . ($data_len + 1));

		//Finally serve data and done ~!
		fseek($file, $start, SEEK_SET);
		$bufsize = 2048000;

		ignore_user_abort(true);
		@set_time_limit(0);
		
		while (!(connection_aborted() || connection_status() == 1) && $data_len > 0) {
			echo fread($file, $bufsize);
			$data_len -= $bufsize;
			flush();
		}

		fclose($file);
		
	}

    function shorten_number($number, $precision = 3, $divisors = null) {

        // Setup default $divisors if not provided
        if (!isset($divisors)) {
            $divisors = array(
                pow(1000, 0) => '', // 1000^0 == 1
                pow(1000, 1) => 'K', // Thousand
                pow(1000, 2) => 'M', // Million
                pow(1000, 3) => 'B', // Billion
                pow(1000, 4) => 'T', // Trillion
                pow(1000, 5) => 'Qa', // Quadrillion
                pow(1000, 6) => 'Qi', // Quintillion
            );
        }

        // Loop through each $divisor and find the
        // lowest amount that matches
        foreach ($divisors as $divisor => $shorthand) {
            if (abs($number) < ($divisor * 1000)) {
                // We found a match!
                break;
            }
        }

        // We found our match, or there were no matches.
        // Either way, use the last defined value for $divisor.
        return number_format($number / $divisor, $precision) . $shorthand;
    }

    function upload_file($keyName,$path) {
        move_uploaded_file($_FILES[$keyName]['tmp_name'],$path);
    }

    function send_header_picture($file_path,$filename_output = '') {

        $filename = basename($file_path);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        if ($filename_output == '')
            $filename_output   =   basename($file_path);

        $type  =   "image/all";

        switch( $file_extension ) {
            case "gif": $type="image/gif"; break;
            case "png": $type="image/png"; break;
            case "jpeg":
            case "jpg": $type="image/jpeg"; break;
            default:
        }

        header('Content-Disposition: attachment; filename="'.$filename_output.'"');
        header('Content-type: ' . $type);
        readfile($file_path);
        exit;

    }

    //@note add encryption key [password_salt] in config.php
    function encrypt($data,$key = '',$bit = 256) {

        $CI = & get_instance();
        $CI->load->library('aes');
        $CI->aes->SetBit($bit);

        if ($key == '')
            $key    =   config_item('password_salt');

        return $CI->aes->Encrypt($data,$key);

    }

    //@note add encryption key [password_salt] in config.php
    function decrypt($data,$bit = 256) {
        $CI = & get_instance();
        $CI->load->library('aes');
        $CI->aes->SetBit($bit);
        return $CI->aes->Decrypt($data,config_item('password_salt'));
    }

    function curl($url,$method = "POST",$fields = array(),$header = array(),$return_transfer = 0,$nobody = FALSE) {

        if (strtolower($method) == 'get')
            $url .= http_build_query($fields);

        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $return_transfer);
        curl_setopt($curl, CURLOPT_NOBODY, $nobody);

        if (strtolower($method) == 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
        }

        if (count($header) > 0)
            curl_setopt($curl, CURLOPT_HTTPHEADER,$header);

        $res = curl_exec ($curl);
        curl_close ($curl);
        return $res;

    }

    //http://ip-api.com/json/
    function ip_info($ip = NULL) {

        $output = NULL;

        if ($ip != $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
                $ip = $_SERVER["REMOTE_ADDR"];
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }

        $data	=	@json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
        //geoplugin_countryCode

        return $data;

    }

    function is_ios() {

        $iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
        $iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");

        if ($iPad || $iPhone || $iPod)
            return TRUE;
        else
            return FALSE;

    }

    function is_android() {
        return $Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
    }

    function is_localhost() {

        $whitelist = array(
            '127.0.0.1',
            '::1',
            'localhost'
        );

        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            return FALSE;
        } else {
            return TRUE;
        }

    }

    function prevent_cache_page() {

        $ci = & get_instance();

        $ci->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $ci->output->set_header('Pragma: no-cache');
        $ci->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    }

    //get user's avatar from gravatar website if that is existing
    function get_gravatar($email,$default,$size=100) {
        $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
        return $grav_url;
    }

    //<editor-fold desc="Location Section">
    function get_latlon_address($lat=NULL,$lng=NULL,$lang='') {

        if ($lat == NULL || $lng == NULL) return;

        $ci = & get_instance();
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?language=' . $lang . '&latlng=' . trim($lat) . ',' . trim($lng) . '&key=' . config_item('googlemap_key_api');
        $json = @file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK") {
            if (count($data->results) > 1)
                return @$data->results[1]->formatted_address;
            else
                return @$data->results[0]->formatted_address;
        } else {
            return '';
        }
    }

    function bearing($lat1, $long1, $lat2, $long2){
        $bearingradians = atan2(asin($long1-$long2)*cos($lat2),
            cos($lat1)*sin($lat2) - sin($lat1)*cos($lat2)*cos($long1-$long2));
        $bearingdegrees = abs(rad2deg($bearingradians));
        return $bearingdegrees;
    }

    //default value for unit is meter
    //m is meter
    //k is kilometer
    //empty is mile
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;

        $unit = strtolower($unit);

        if ($unit == 'm' || $unit == '')
            return $meters;

        if ($unit == 'k')
            return $kilometers;

        if ($unit == 'y')
            return $yards;

    }

    function get_location_distance($lat1,$long1,$lat2,$long2,$lang) {

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=$lang&key=".config_item('googlemap_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = @$response_a['rows'][0]['elements'][0]['distance']['text'];
        $time = @$response_a['rows'][0]['elements'][0]['duration']['text'];

        if ($dist == NULL)  $dist   =   0;
        if ($time == NULL)  $time   =   0;

        return array('distance' => $dist, 'time' => $time);

    }
    //</editor-fold>

    function get_percent_value($value,$percent) {

        if (!validate_int($value))
            return $value;

        if ($percent == 0)
            return $value;

        $temp   =   $value * $percent / 100;
        $temp   =   $value - $temp;

        return $temp;

    }

    //register recaptcha in https://www.google.com/recaptcha
    //you have to add <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div> in your form
    //add below link to header of page
    //<script src='https://www.google.com/recaptcha/api.js?hl=en'></script>
    //you can change language with hl parameter
    function check_google_robot() {

        if (is_localhost()) return TRUE;

        $secret_key     =   config_item('google_robot_secret_key');
        $response_key   =   $_POST["g-recaptcha-response"];
        $user_ip        =   $_SERVER['REMOTE_ADDR'];

        $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$response_key&remoreip=$user_ip";
        $url = file_get_contents($url);
        $url = json_decode($url);
        if($url -> success) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    function is_https_on() {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {

            return true;
        }
        return false;
    }

    function clean_string($string) {

        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    }

    //0 is همراه اول
    //1 is ایرانسل
    //2 is رایتل
    function detect_simcard_operator($number) {

        if (substr($number,0,3) == '091' || substr($number,0,3) == '099')
            return 0;

        if (substr($number,0,3) == '093' || substr($number,0,4) == '0901' || substr($number,0,4) == '0902' || substr($number,0,4) == '0903')
            return 1;

        if (substr($number,0,4) == '0921' || substr($number,0,4) == '0922')
            return 2;

    }

    function get_segment($segment) {

        $ci = & get_instance();

        return $ci->uri->segment($segment);

    }

    function clean_special_characters($string) {

        $string = str_replace(' ', '-', $string);

        // Removes special chars.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        // Replaces multiple hyphens with single one.
        $string = preg_replace('/-+/', '-', $string);

        return $string;

    }

    function force_lowercase_urls() {
        // Grab requested URL
        $url = $_SERVER['REQUEST_URI'];
        // If URL contains a period, halt (likely contains a filename and filenames are case specific)
        if ( preg_match('/[\.]/', $url) ) {
            return;
        }
        // If URL contains a question mark, halt (likely contains a query variable)
        if ( preg_match('/[\?]/', $url) ) {
            return;
        }
        if ( preg_match('/[A-Z]/', $url) ) {
            // Convert URL to lowercase
            $lc_url = strtolower($url);
            // 301 redirect to new lowercase URL
            header('Location: ' . $lc_url, TRUE, 301);
            exit();
        }
    }
	
	function get_percentage($total, $number)
    {
      if ( $total > 0 ) {
       return round($number / ($total / 100),2);
      } else {
        return 0;
      }
    }