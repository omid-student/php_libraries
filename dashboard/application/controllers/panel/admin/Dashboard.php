<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Dashboard extends CI_Controller {

        var $admin_id   =   '';

        function __construct() {
            parent::__construct();
            $this->load->model('Users/Admin','admin');
            $this->admin_id =   $this->admin->check_login();
        }

        function index() {
            $this->admin->show_page('dashboard',NULL,'menu_dashboard');
        }

        function profile() {
            $row = $this->db->where('username',$this->admin_id)->get('admin')->row_array();
            $this->admin->show_page('profile/profile',$row);
        }

        function logout() {

            $this->db->where('username',$this->admin_id)->set('token','')->update('admin');

            $this->session->unset_userdata('is_login');
            $this->session->unset_userdata('user_token');
            $this->session->sess_destroy();

            redirect(base_url('panel/admin/login'));

        }

        function change_password() {

            $old_password       =   $this->input->post('old_password');
            $new_password       =   $this->input->post('new_password');
            $confirm_password   =   $this->input->post('confirm_password');

            $password           =   $this->db->where('username',$this->admin_id)->get('admin')->row()->password;

            if (!password_verify($old_password,$password))
                $this->admin->show_message('خطا','رمز عبور قبلی اشتباه میباشد','danger');

            if (!preg_match('/^[\S|\s]{5,}$/',$new_password))
                $this->admin->show_message('خطا','رمز عبور جدید را حداقل 5 حرفی وارد کنید','danger');

            if ($new_password !== $confirm_password)
                $this->admin->show_message('خطا','رمز عبور جدید یکسان نیستند','danger');

            $this->db->set('password',password_hash($new_password,PASSWORD_DEFAULT))
                     ->set('token','')
                     ->where('username',$this->admin_id);
            $this->db->update('admin');

            $this->admin->show_message('توجه','رمز عبور شما با موفقیت تغییر کرد<br>لطفا دوباره وارد حساب کاربری شوید','success',base_url('panel/admin/login'));

        }

        function change_profile() {

            $full_name       =   $this->input->post('full_name');

            if (strlen(trim($full_name)) < 3)
                $this->admin->show_message('خطا','نام مدیریت نباید کمتر از 3 حرف باشد','danger');

            $this->db->set('full_name',$full_name)
                ->where('username',$this->admin_id);
            $this->db->update('admin');

            $this->admin->show_message('توجه','تغییرات با موفقیت اعمال شد','success');

        }

        function template_list() {

        }

    }