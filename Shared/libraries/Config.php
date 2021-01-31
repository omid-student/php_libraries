<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    class Config extends CI_Library {
    
        function __construct() {
            parent::__construct();
			$this->db->query('
				CREATE TABLE IF NOT EXISTS `tbl_config` (
				  `key` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
				  `value` varchar(5000) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
				  PRIMARY KEY (`key`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
        }

		function set($key,$value) {

            $res = $this->db->where('key',$key)->set('value',$value)->update('config');

            if ($this->db->affected_row() > 0) {
				$this->db->cache_delete('config', 'set');
				return TRUE;
			}
			else
				return FALSE;

        }
		
        function get($key) {

			$this->db->cache_on();
            $res = $this->db->where('key',$key)->get('config');
			$this->db->cache_off();
			
            if ($res->num_rows() == 0)
                return '';
            else
                return $res->row()->value;

        }

        function get_all($assoc = TRUE) {

            $result = $this->db->get('config')->result_array();

            if ($assoc)
                return $result;
            else {

                $row = array();

                foreach ($result as $item) {
                    $row[$item['key']]  =   $item['value'];
                }

                return $row;
            }

        }
    
    }