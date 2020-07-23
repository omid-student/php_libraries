<?php
/**
 * Created by PhpStorm.
 * User: Omid Aghakhani
 * Date: 8/6/2019
 * Time: 4:18 PM
 */

class MY_Input extends CI_Input
{

    function is_localhost() {

		if ($this->ip_address() == '::1' || $this->ip_address() == 'localhost')
			return TRUE;
		else
			return FALSE;
		
	}

	function referer_url($default = '') {

        $referer = @$_SERVER['HTTP_REFERER'];
        if ($referer == '')
            return $default;
        else
            return $referer;
        
    }

    function get_referer() {
        return $this->referer_url();
    }

}