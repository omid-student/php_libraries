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