<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Login extends CI_Controller {

        function __construct() {
            parent::__construct();
            $this->load->library('session');
        }

        function index() {

            $token  =   md5(uniqid().$this->input->ip_address());
            $this->session->set_flashdata('token',$token);

            $this->load->view('admin/auth/login',array('token' => $token));

        }

        function try() {

            if (!$this->session->flashdata('token'))
                redirect('panel/admin/login?invalid_token');

            if ($this->session->flashdata('token') != $this->input->post('token'))
                redirect('panel/admin/login?invalid_user_token');

            $username   =   $this->input->post('username',TRUE);
            $password   =   $this->input->post('password',TRUE);

            if (!filter_var($username,FILTER_VALIDATE_EMAIL))
                redirect('panel/admin/login?error=INVALID_USERNAME');

            if (!preg_match('/^[\S|\s]{5,}$/',$password))
                redirect('panel/admin/login?error=INVALID_PASSWORD');

            $this->db->where('username',$username);
            $result =   $this->db->get('admin');
            if ($result->num_rows() == 0)
                redirect('panel/admin/login?error=NOT_FOUND');

            $admin_password =   $result->row()->password;
            $user_password  =   $password;

            if (!password_verify($user_password,$admin_password))
                redirect('panel/admin/login?INVALID_PASSWORD');

            if (ENVIRONMENT == 'development')
                $code   =   12345;
            else
                $code   =   rand(111111,9999999);

            $this->session->set_userdata('code',$code);
            $this->session->mark_as_temp('code',300);

            $this->session->set_userdata('mobile',$result->row()->mobile);
            $this->session->mark_as_temp('mobile',300);

            $this->sms->set_mobile($result->row()->mobile);
            $this->sms->set_token(config_item('smsir_apikey'));
            $this->sms->set_secret_key(config_item('smsir_secretkey'));
            $this->sms->smsir->set_template_id(SMS_CONFIRMATION_CODE);
            $this->sms->smsir->add_parameter('value',$code);
            $this->sms->smsir->send();

            $this->db->where('username',$username);
            $this->db->set('code',$code);
            $this->db->update('admin');

            redirect(base_url('panel/admin/login/confirm'));

        }

        function confirm() {

            if (!$this->session->userdata('code'))
                redirect(base_url('panel/admin/login?NOT_LOGIN'));

            $token  =   md5(uniqid().$this->input->ip_address());
            $this->session->set_flashdata('token',$token);

            $this->load->view('admin/auth/confirm',array('token' => $token));

        }

        function check_confirm() {

            if (!$this->session->flashdata('token'))
                redirect(base_url('panel/admin/login?NOT_FOUND_TOKEN'));

            if ($this->session->flashdata('token') != $this->input->post('token'))
                redirect(base_url('panel/admin/login?INVALID_TOKEN'));

            $code   =   $this->input->post('code',TRUE);
            if (!is_numeric($code))
                redirect(base_url('panel/admin/login/confirm?error=NOT_FOUND_CODE'));

            if ($this->session->tempdata('code') != $code)
                redirect(base_url('panel/admin/login/confirm?error=INVALID_CODE'));

            $mobile     =   $this->session->tempdata('mobile');

            $new_token  =   md5($code.$mobile);

            $this->db->where('mobile',$mobile);
            $this->db->set('token',$new_token);
            $this->db->update('admin');

            $this->session->set_userdata('is_login', TRUE);
            $this->session->set_userdata('time',time());
            $this->session->set_userdata('user_token',$new_token);

            redirect('panel/admin/dashboard');

        }

    }