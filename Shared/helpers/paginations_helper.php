<?php defined('BASEPATH') OR exit('No direct script access allowed');

    function prepare_pagination($total_rows,$base_url,$link_count = 10) {

        $ci = & get_instance();

        $config['total_rows']       =   $total_rows;
        $config['per_page']         =   20;
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
        $config['prev_link']        =   'قبلی';
        $config['prev_tag_open']    =   '<li>';
        $config['next_link']        =   'قبلی';
        $config['next_tag_open']    =   '<li>';
		$config['last_link']        =   'آخرین';
        $config['first_link']       =   'اولین';
        $config['num_links']        =   $link_count;

        $ci->load->library('pagination');
        $ci->pagination->initialize($config);
        return $ci->pagination->create_links();

    }