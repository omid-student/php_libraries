<?php
/**
 * Created by PhpStorm.
 * User: Omid Aghakhani
 * Date: 6/6/2020
 * Time: 6:48 PM
 */

function redirect_ssl() {
    $CI =& get_instance();
    $class = $CI->router->fetch_class();
    $exclude =  array();  // add more controller name to exclude ssl.
    if(!in_array($class,$exclude)) {
        // redirecting to ssl.
		if (strpos($CI->config->config['base_url'],'localhost') !== FALSE) return;
        $url = $CI->config->config['base_url'];
        $CI->config->config['base_url'] = str_replace('http://', 'https://', $CI->config->config['base_url']);
        $CI->config->config['base_url'] = str_replace('https://www.','https://',$CI->config->config['base_url']);
        if ($_SERVER['SERVER_PORT'] != 443 || strpos($url,'www.') !== FALSE) redirect($CI->uri->uri_string());
    } else {
        // redirecting with no ssl.
		if (strpos($CI->config->config['base_url'],'localhost') !== FALSE) return;
        $CI->config->config['base_url'] = str_replace('https://', 'http://', $CI->config->config['base_url']);
        if ($_SERVER['SERVER_PORT'] == 443) redirect($CI->uri->uri_string());
    }
}