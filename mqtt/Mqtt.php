<?php if (! defined('BASEPATH') ) exit("Not Allow to access this Page");

    /**
    * subscribe and publish to mqtt broker
    *
    * @property $server string ip or domain name
    * @property $port int port number
    * @property $username string if authentication is enable
    * @property $password string if authentication is enable
    * @property $client_id string is unique for each user
    * @property $keep_alive int is timer for keep connection
    * @property $will array(qos,retain,topic,content)
    */
    class Mqtt
    {

        private $server;
        private $port;
        private $username;
        private $password;
        private $client_id;
        private $mqtt;
        private $topics = array();
        private $parent;
        private $keep_alive = 10;
        private $timeout = -1;
        private $start_time;
        private $will = NULL;

        function __construct() {
            require(APPPATH . "libraries/MqttCore.php");
            $this->client_id = uniqid();
        }

        function __set($name, $value) {

            if ($name == 'server')
                $this->server = $value;

            if ($name == 'port')
                $this->port = $value;

            if ($name == 'username')
                $this->username = $value;

            if ($name == 'password')
                $this->password = $value;

            if ($name == 'client_id' || $name == 'clientid')
                $this->client_id = $value;

            if ($name == 'client_id' || $name == 'clientid')
                $this->client_id = $value;

            if ($name == 'timeout')
                $this->timeout   =   $value;

            if ($name == 'keep_alive') {
                if ($value == -1)
                    $value = 1000000000000;
                $this->keep_alive = $value;
            }

            if ($name == 'will') {
                $this->will = $value;

            }

        }

        function set_parent($parent) {
            $this->parent   =   $parent;
        }

        function connect() {

            $this->mqtt = new phpMQTT($this->server, $this->port, $this->client_id,$this);
            $this->mqtt->keepalive = $this->keep_alive;
            if (@$this->mqtt->connect(TRUE, $this->will, $this->username, $this->password) == FALSE) {
                return FALSE;
            }

            return TRUE;

        }

        function disconnect() {
            $this->mqtt->disconnect();
        }

        function subscribe($topic_name, $qos,$event) {

            $topics[$topic_name]    =   array("qos" => $qos, "function" => "on_message",'event' => $event);
            $this->topics[]         =   $topics;

            $this->mqtt->subscribe($topics, $qos);

            return $this;

        }

        function publish($topic_name,$message,$qos) {
            $this->mqtt->publish($topic_name, $message, $qos);
        }

        /**
         * dont use this when call publish method
         * only use for subscribe when you want to listen
         */
        function listen() {

            $this->start_time   =   time();

            while ($this->mqtt->proc()) {
				
				if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
				    if (function_exists('pcntl_signal_dispatch')) {
                        @pcntl_signal_dispatch(); //use this for handle signal from linux
                    }
				}

				if ($this->timeout != -1)
				    if (time() - $this->start_time > $this->timeout) {
                        return FALSE;
                    }
				
            }

            $this->mqtt->close();

            return $this;

        }

        function on_message($topic, $msg,$event) {
            call_user_func(array($this->parent,$event),$topic,$msg);
        }

    }
   
?>
