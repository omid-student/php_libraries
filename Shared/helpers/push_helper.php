<?php defined('BASEPATH') OR exit('No direct script access allowed');

    /***
     * send push to android or ios os
     * add firebase_api config in config/config.php file
     * firebase_api argument is array
     * for change notification sound,add sound field in payload
     * for add badge icon for ios,add badge field in payload
     * @param string $token_or_topic
     * @param array  $payload each field max length is 200 character
     * @param int    $expire
     * @param bool   $silent
     * @return bool
     */
    function send_push($token_or_topic,$payload = array(),$expire = 0,$silent = FALSE) {

        $apis	=	config_item('firebase_api');

        $fields = array
        (
            'data'				=> $payload
        );

        if (filter_var($expire,FILTER_VALIDATE_INT)) {
            if ($expire != 0)
                $fields['time_to_live'] = $expire;
        }

        //check is token or topic
        if (strlen($token_or_topic) > 100)
            $token_info =   get_token_info($token_or_topic);
        else
            $token_info =   FALSE;

        //set topic name or token
        if ($token_info == FALSE) {
            $fields['to'] = "/topics/$token_or_topic";
        }
        else {
            $fields['registration_ids'] =  array($token_or_topic);
        }

        if ($silent == FALSE) {
            $fields['notification']         =   $payload;
        } else {
            $fields['content_available']    =   TRUE;
        }

        foreach($apis as $api) {

            $headers = array
            (
                'Authorization: key=' . $api,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);

        }

        return TRUE;

    }

    function get_token_info($token) {

        $api_key    =   config_item('firebase_api');

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Accept-language: en\r\n" .
                    "Authorization: key=$api_key\r\n"
            ]
        ];

        $context = stream_context_create($opts);

        $result = @file_get_contents('https://iid.googleapis.com/iid/info/'.$token, false, $context);

        if (json_decode($result) === FALSE)
            return FALSE;
        else
            return json_decode($result);

    }