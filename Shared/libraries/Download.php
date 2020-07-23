<?php 

/**
$this->load->library('download');
$this->download->setMime($path);
$this->download->setForce(TRUE);
$this->download->DownloadFile($path);
**/

class Download {
	
	var $mime = '';
	var $isForce = TRUE;
	
	function set_range($range, $filesize, &$first, &$last){
		$dash=strpos($range,'-');
		$first=trim(substr($range,0,$dash));
		$last=trim(substr($range,$dash+1));
		if ($first=='') {
			//suffix byte range: gets last n bytes
			$suffix=$last;
			$last=$filesize-1;
			$first=$filesize-$suffix;
			if($first<0) $first=0;
		} else {
			if ($last=='' || $last>$filesize-1) $last=$filesize-1;
		}
		if($first>$last){
			//unsatisfiable range
			header("Status: 416 Requested range not satisfiable");
			header("Content-Range: */$filesize");
			exit;
		}
	}

	function buffered_read($file, $bytes, $buffer_size=1024){
		/*
		Outputs up to $bytes from the file $file to standard output, $buffer_size bytes at a time.
		*/
		$bytes_left=$bytes;
		while($bytes_left>0 && !feof($file)){
			if($bytes_left>$buffer_size)
				$bytes_to_read=$buffer_size;
			else
				$bytes_to_read=$bytes_left;
			$bytes_left-=$bytes_to_read;
			$contents=fread($file, $bytes_to_read);
			echo $contents;
			flush();
		}
	}

	function DownloadFile($filename){
		/*
		Byteserves the file $filename.  

		When there is a request for a single range, the content is transmitted 
		with a Content-Range header, and a Content-Length header showing the number 
		of bytes actually transferred.

		When there is a request for multiple ranges, these are transmitted as a 
		multipart message. The multipart media type used for this purpose is 
		"multipart/byteranges".
		*/

		$filesize=filesize($filename);
		$file=fopen($filename,"rb");

		$ranges=NULL;
		if ($_SERVER['REQUEST_METHOD']=='GET' && isset($_SERVER['HTTP_RANGE']) && $range=stristr(trim($_SERVER['HTTP_RANGE']),'bytes=')){
			$range=substr($range,6);
			$boundary='g45d64df96bmdf4sdgh45hf5';//set a random boundary
			$ranges=explode(',',$range);
		}

		if($ranges && count($ranges)){
			header("HTTP/1.1 206 Partial content");
			header("Cache-Control: private, max-age=10800, pre-check=10800");
			header("Pragma: private");
			header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
			header("Accept-Ranges: bytes");
			
			if(count($ranges)>1){
				/*
				More than one range is requested. 
				*/

				//compute content length
				$content_length=0;
				foreach ($ranges as $range){
					$this->set_range($range, $filesize, $first, $last);
					$content_length+=strlen("\r\n--$boundary\r\n");
					$content_length+=strlen("Content-type: ".$this->mime."\r\n");
					$content_length+=strlen("Content-range: bytes $first-$last/$filesize\r\n\r\n");
					$content_length+=$last-$first+1;          
				}
				$content_length+=strlen("\r\n--$boundary--\r\n");

				//output headers
				header("Content-Length: $content_length");
				header("Cache-Control: private, max-age=10800, pre-check=10800");
				header("Pragma: private");
				header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
				//see http://httpd.apache.org/docs/misc/known_client_problems.html for an discussion of x-byteranges vs. byteranges
				header("Content-Type: multipart/x-byteranges; boundary=$boundary");
				header("Filename: ".basename($filename));
				//output the content
				foreach ($ranges as $range){
					$this->set_range($range, $filesize, $first, $last);
					echo "\r\n--$boundary\r\n";
					echo "Content-type: ".$this->mime."\r\n";
					echo "Content-range: bytes $first-$last/$filesize\r\n\r\n";
					fseek($file,$first);
					$this->buffered_read ($file, $last-$first+1);          
				}
				echo "\r\n--$boundary--\r\n";
			} else {
				/*
				A single range is requested.
				*/
				$range=$ranges[0];
				$this->set_range($range, $filesize, $first, $last);  
				header("Content-Length: ".($last-$first+1) );
				header("Content-Range: bytes $first-$last/$filesize");
				header("Content-Type: ".$this->mime."");
				header("Cache-Control: private, max-age=10800, pre-check=10800");
				header("Pragma: private");
				header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
				header('Content-disposition: attachment; filename='.basename($filename));		
				fseek($file,$first);
				$this->buffered_read($file, $last-$first+1);
			}
		} else{
			//no byteserving
			if ($this->isForce == TRUE)
				header('Content-disposition: attachment; filename='.basename($filename));
			header("Accept-Ranges: bytes");
			header("Content-Length: $filesize");
			header("Cache-Control: private, max-age=10800, pre-check=10800");
			header("Pragma: private");
			header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
			header("Content-Type: ".$this->mime."");
			readfile($filename);
		}
		
		fclose($file);
	}

	function serve($filename, $download=0){
		//Just serves the file without byteserving
		//if $download=true, then the save file dialog appears
		$filesize=filesize($filename);
		header("Content-Length: $filesize");
		header("Content-Type: ".$this->mime."");
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
		$filename_parts=pathinfo($filename);
		if($download) header('Content-disposition: attachment; filename='.$filename_parts['basename']);
		readfile($filename);
	}
	
	function setMime($filename) {
		$this->mime = $this->get_mime($filename);
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
	
	function setForce($state) {
		$this->isForce = $state;
	}

}

?>