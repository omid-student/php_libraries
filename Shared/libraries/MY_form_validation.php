<?php if (! defined('BASEPATH') ) exit("Not Allow to access this Page");
    
    class MY_form_validation extends CI_Form_validation {

		
        public function __construct($data = array()) {

			parent::__construct();

			$this->set_data($data);

			$this->set_rules('first_name','نام','required|min_length[3]');
            $this->set_rules('last_name','نام خانوادگی','required|min_length[3]');
            $this->set_rules('mobile','شماره موبایل','required|exact_length[11]|numeric');
			
		}
		
    }