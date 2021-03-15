<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    class Uploader extends CI_Model {

        var $path = 'files/upload';

        function __construct() {
			
            parent::__construct();
			
			@mkdir($this->path);
            $this->path =   $this->path.'/';

            $this->db->query('
                CREATE TABLE IF NOT EXISTS `tbl_upload` (
                  `pid` int(11) NOT NULL AUTO_INCREMENT,
                  `file_name` varchar(200) NOT NULL,
                  `file_type` varchar(100) NOT NULL,
                  `file_size` INT NOT NULL NOT NULL,
                  `date_registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`pid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;');
				
        }
		
		function set_upload_path($path) {
			$this->path = $path;
		}

        function upload() {

            $files  =   $_FILES;

            foreach ($files as $file) {

                $file_type  =   $file['type'];
                $file_name  =   $file['name'];
                $file_size  =   $file['size'];

                move_uploaded_file($file['tmp_name'],"{$this->path}$file_name");

                $this->db->set('file_name',$file_name)->set('file_type',$file_type)->set('file_size',$file_size);
                $this->db->insert('upload');

            }

            return TRUE;

        }

        function get_file_link($file_name) {

            $link   =   base_url($this->path.$file_name);
            $link   =   str_replace('index.php','',$link);

            return $link;

        }

        function delete($pid) {

            $res = $this->db->where('pid',$pid)->get('upload')->row();
            @unlink("{$this->path}{$res->filename}");
            $this->db->where('pid',$pid)->delete('upload');

        }

    }