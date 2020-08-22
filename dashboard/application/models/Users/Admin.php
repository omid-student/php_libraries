<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    class Admin extends CI_Model {

        var $admin_info;

        function __construct() {
            parent::__construct();
            $ci = & get_instance();
            $ci->load->library('session');
        }

		function install_table() {

            $this->db->query("DROP TABLE IF EXISTS `tbl_admin`;
            CREATE TABLE IF NOT EXISTS `tbl_admin` (
              `pid` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `password` varchar(130) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `full_name` varchar(120) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `mobile` char(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
              `code` int(11) DEFAULT NULL,
              `token` varchar(300) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
              `status` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '1',
              PRIMARY KEY (`pid`),
              UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");

		    $this->db->query("
INSERT INTO `tbl_admin` (`pid`, `username`, `password`, `full_name`, `mobile`, `code`, `token`, `status`) VALUES
(1, 'test@yahoo.com', '$2y$10$/yoHN5L.IOqaNky99E9rR.ej5KUZ7H25stYj4i7CCIsausrRNEhMK', 'امید اقاخانی2', '09124952735', 12345, '', '1');");

        }

        function check_login() {

            $ci = & get_instance();

            if (!$ci->session->userdata('is_login') || $ci->session->userdata('is_login') == FALSE)
                $this->_failed_login('NOT_LOGIN');

            if (!$ci->session->userdata('user_token') || $ci->session->userdata('user_token') == '')
                $this->_failed_login('NOT_FOUND_TOKEN');

            $token  =   $ci->session->userdata('user_token');

            $result =   $ci->db->where('token',$token)->get('admin');
            if ($result->num_rows() == 0)
                $this->_failed_login('NOT_FOUND_ADMIN');

            $new_token  =   md5($result->row()->code.$result->row()->mobile);
            if ($new_token != $token)
                $this->_failed_login('NOT_EQUAL_TOKEN');

            $this->admin_info   =   $result->row();

            return $result->row()->username;

        }

        function show_page($page,$data = array(),$menu = '',$sub_menu = '') {

            $data['admin_info']                 =   $this->admin_info;

            if ($menu != '')
                $data[$menu]                    =   'active nav-active';

            if ($sub_menu != '') {
                $data[$sub_menu] = 'active';
                $data["class_id"] = $menu;
            }

            $this->load->view('admin/master/header',$data);
            $this->load->view('admin/'.$page,$data);
            $this->load->view('admin/master/footer');

        }

        function show_message($title,$message,$type,$url = '') {

            if ($title == '')
                $title  =   'توجه';

            $ci = & get_instance();

            if ($url == '')
                $url    =   @$_SERVER['HTTP_REFERER'];

            $ci->session->set_flashdata('message',array('title' => $title,'message' => $message,'type' => $type));

            redirect($url);

        }

        function prepare_pagination($total_rows,$base_url,$link_count = 10) {

            $ci = & get_instance();

            $config['total_rows']       =   $total_rows;
            $config['per_page']         =   config_item('per_page');
            $config['base_url']         =   $base_url;
            $config['full_tag_open']    =   '<ul class="pagination">';
            $config['full_tag_close']   =   '</ul>';
            $config['num_tag_open']     =   '<li class="page-link">';
            $config['num_tag_close']    =   '</li>';
            $config['cur_tag_open']     =   '<li class="page-link"><span style="background-color:transparent">';
            $config['cur_tag_close']    =   '</span></li>';
            $config['next_link']        =   'بعدی';
            $config['next_tag_open']    =   '<li  class="page-link">';
            $config['next_tag_close']   =   '</li>';
            $config['prev_link']        =   '';
            $config['prev_tag_open']    =   '<li class="page-link">';
            $config['prev_tag_close']    =   '</li>';
            $config['next_link']        =   '';
            $config['next_tag_open']    =   '<li>';
            $config['num_links']        =   $link_count;

            $ci->load->library('pagination');
            $ci->pagination->initialize($config);
            return $ci->pagination->create_links();

        }

        function _failed_login($error = '') {

            $ci = & get_instance();

            $ci->session->unset_userdata('is_login');
            $ci->session->unset_userdata('user_token');
            $ci->session->sess_destroy();

            redirect(base_url('panel/admin/login?error='.$error));

        }

    }