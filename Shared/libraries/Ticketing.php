<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

    class Ticketing extends CI_Model {

        const PRIORITY_LOW = 1;
        const PRIORITY_MEDIUM = 2;
        const PRIORITY_HIGH = 3;
        const PRIORITY_EMERGENCY = 4;

        const STATUS_NEW = 0;
        const STATUS_OPEN = 1;
        const STATUS_REPLY_ADMIN = 2;
        const STATUS_REPLY_USER = 3;
        const STATUS_REPLY_WAITING = 4;
        const STATUS_REPLY_CLOSE = 5;

        function __construct() {
            parent::__construct();
            include APPPATH.'helpers/jdf.php';
        }

        function add($user_id,$subject,$text,$department_id,$priority) {

            $ticket_id  =   $this->generate_ticket_id($user_id);

            $this->db->set('pid',$ticket_id)
                     ->set('user_id',$user_id)
                     ->set('department_id',$department_id)
                     ->set('subject',$subject)
                     ->set('text',$text)
                     ->set('priority',$priority)
                     ->set('is_main',1)
                     ->set('status',self::STATUS_NEW)
                     ->insert('ticket');

            $this->add_attachment($ticket_id);

            return $this->db->affected_rows() == 0 ? FALSE : TRUE;

        }

        function reply($ticket_id,$text,$is_admin = FALSE,$admin_id = NULL) {

            $ticket_info    =   $this->info($ticket_id);

            $new_ticket_id  =   $this->generate_ticket_id($ticket_info->user_id);

            $this->db->set('pid',$new_ticket_id)
                     ->set('user_id',$ticket_info->user_id)
                     ->set('text',$text)
                     ->set('parent_id',$ticket_id)
                     ->set('is_admin',intval($is_admin))
                     ->set('admin_id',$admin_id)
                     ->set('seen',0)
                     ->insert('ticket');

            if ($is_admin == TRUE) {
                $this->db->where('pid',$ticket_id)->set('status',self::STATUS_REPLY_ADMIN)->update('ticket');
            } else {
                $this->db->where('pid',$ticket_id)->set('status',self::STATUS_REPLY_USER)->update('ticket');
            }

            $this->add_attachment($new_ticket_id);

            return $this->db->affected_rows() == 0 ? FALSE : TRUE;

        }

        /**
         * set $sub_tickets to TRUE for delete all sub parent tickets
         * @param $ticket_id
         * @param bool $sub_tickets
         */
        function delete($ticket_id,$sub_tickets = FALSE) {

            $this->db->where('pid',$ticket_id)->delete('ticket');

            if ($sub_tickets) {
                $this->db->where('parent_id',$ticket_id)->delete('ticket');
            }

        }

        function info($ticket_id) {

            $this->db->select('*,(SELECT title FROM tbl_ticket_department WHERE pid = tbl_ticket.department_id) AS department_title');
            $info                   =   $this->db->where('pid',$ticket_id)->get('ticket')->row();

            $info->status_title     =   $this->get_status_title($info->status);
            $info->priority_title   =   $this->get_status_title($info->priority);

            return $info;

        }

        function get($department_id = NULL,$priority = NULL,$status = FALSE) {

            if ($priority != NULL)
                $this->db->where('priority',$priority);

            if ($status !== NULL) {
                if (is_array($status))
                    $this->db->where_in('status', $status);
                else
                    $this->db->where('status', $status);
            }

            if ($department_id!= NULL)
                $this->db->where('department_id',$department_id);

            $this->db->select('*');
            $this->db->select('(SELECT title FROM tbl_ticket_department WHERE pid = tbl_ticket.department_id) AS department_title');
            $this->db->where('is_main',1);
            $this->db->order_by('date_created','DESC');
            $result =   $this->db->get('ticket')->result_array();

            for ($i = 0 ; $i < count($result) ; $i++) {
                $result[$i]['status_title']     =   $this->get_status_title($result[$i]['status']);
                $result[$i]['priority_title']   =   $this->get_status_title($result[$i]['priority']);
            }

            return $result;

        }

        function get_by_user($user_id,$department_id = NULL,$priority = NULL,$status = FALSE) {

            if ($priority != NULL)
                $this->db->where('priority',$priority);

            if ($status != NULL)
                $this->db->where('status',$status);

            if ($department_id!= NULL)
                $this->db->where('department_id',$department_id);

            $this->db->select('*,(SELECT title FROM tbl_ticket_department WHERE pid = tbl_ticket.department_id) AS department_title');
            $this->db->where('user_id',$user_id);
            $this->db->where('is_main',1);
            $this->db->order_by('date_created','DESC');
            $result =   $this->db->get('ticket')->result_array();

            for ($i = 0 ; $i < count($result) ; $i++) {
                $result[$i]['status_title']     =   $this->get_status_title($result[$i]['status']);
                $result[$i]['priority_title']   =   $this->get_status_title($result[$i]['priority']);
            }

            return $result;

        }

        /**
         * get user or admin replies and set seen all messages
         * @param $ticket_id
         * @param bool $open_by_admin
         * @return array
         */
        function get_replies($ticket_id,$open_by_admin = FALSE) {

            $this->db->select('*');
            $this->db->select('(SELECT fullname FROM tbl_admin WHERE pid = tbl_ticket.admin_id) AS admin_name');
            $this->db->where('parent_id',$ticket_id);
            $this->db->or_where('pid',$ticket_id);
            $this->db->order_by('date_created','DESC');
            $result =   $this->db->get('ticket')->result_array();

            if ($open_by_admin) {

                if ($this->info($ticket_id)->status == self::STATUS_NEW) {
                    $this->db->where('pid', $ticket_id)->set('status', self::STATUS_OPEN)->update('ticket');
                }

                $this->db->where('is_admin',0);
                $this->db->where('parent_id',$ticket_id)->set('seen',1)->update('ticket');

            } else {
                $this->db->where('is_admin',1);
                $this->db->where('parent_id',$ticket_id)->set('seen',1)->update('ticket');
            }

            for ($i = 0 ; $i < count($result) ; $i++) {
                $result[$i]['attachments']     =   $this->get_attachment($result[$i]['pid']);
            }

            return $result;

        }

        /**
         * get unread message for admin or user
         * @param bool $only_main_ticket,if it's true so return only tickets with replies count
         * @param bool $only_users_reply,return only admin or users replies
         * @return array
         */
        function get_new_replies($only_main_ticket = FALSE,$only_users_reply = FALSE) {

            $this->db->select('*');
            $this->db->select('(SELECT fullname FROM tbl_admin WHERE pid = tbl_ticket.admin_id) AS admin_name');
            $this->db->where('is_main',0);
            if ($only_users_reply == TRUE) {
                $this->db->where('user_id',$only_users_reply);
                $this->db->where('is_admin',0);
            }

            $this->db->where('seen',0);
            $this->db->order_by('date_created','DESC');
            $result =   $this->db->get('ticket')->result_array();

            if (count($result) == 0)
                return $result;

            if ($only_main_ticket) {

                $ids    =   array();

                foreach ($result as $item) {
                    $ids[]  =   $item['parent_id'];
                }

                $this->db->select('*');
                $this->db->select('(SELECT title FROM tbl_ticket_department WHERE pid = tbl_ticket.department_id) AS department_title');
                $this->db->select('COUNT(parent_id) AS replies_count');
                $this->db->where('is_main',1);
                $this->db->where_in('pid',$ids);
                $this->db->group_by('parent_id');
                $this->db->order_by('date_created','DESC');
                $result =   $this->db->get('ticket')->result_array();

            }

            for ($i = 0 ; $i < count($result) ; $i++) {
                $result[$i]['attachments']     =   $this->get_attachment($result[$i]['pid']);
            }

            return $result;

        }

        function set_waiting($ticket_id) {

            $this->db->set('status',self::STATUS_REPLY_WAITING);
            $this->db->where('pid',$ticket_id);
            $this->db->update('ticket');

            return $this->db->affected_rows() == 0 ? FALSE : TRUE;

        }

        function close_ticket($ticket_id) {

            $this->db->set('status',self::STATUS_REPLY_CLOSE);
            $this->db->where('pid',$ticket_id);
            $this->db->update('ticket');

            return $this->db->affected_rows() == 0 ? FALSE : TRUE;

        }

        function seen_ticket($ticket_id) {

            $this->db->set('seen',1);
            $this->db->where('parent_id',$ticket_id);
            $this->db->or_where('pid',$ticket_id);
            $this->db->update('ticket');

            return $this->db->affected_rows() == 0 ? FALSE : TRUE;

        }

        function get_priority_title($priority) {

            if ($priority == self::PRIORITY_LOW)
                return 'پایین';

            if ($priority == self::PRIORITY_MEDIUM)
                return 'معمولی';

            if ($priority == self::PRIORITY_HIGH)
                return 'بالا';

            if ($priority == self::PRIORITY_EMERGENCY)
                return 'اورژانسی';

        }

        function get_status_title($status) {

            if ($status == self::STATUS_NEW)
                return 'جدید';

            if ($status == self::STATUS_REPLY_CLOSE)
                return 'بسته شده';

            if ($status == self::STATUS_REPLY_WAITING)
                return 'در انتظار';

            if ($status == self::STATUS_OPEN)
                return 'باز شده';

            if ($status == self::STATUS_REPLY_USER)
                return 'در دست بررسی';

            if ($status == self::STATUS_REPLY_ADMIN)
                return 'پاسخ پشتیبان';

        }

        function get_persian_date($date) {

            $r = strtotime($date);
            return jdate('Y F d (H:i)',$r);

        }

        function add_attachment($ticket_id) {

            if (!isset($_FILES['file']))
                return FALSE;

            if ($_FILES['file']['size'] < 100)
                return FALSE;

            $filename                   =   $ticket_id.date('His',time());
            $filename                  .=   '.'.pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);

            $config['file_name']        =   $filename;
            $config['allowed_types']    =   'jpg|pdf|jpeg';
            $config['overwrite']        =   TRUE;
            $config['max_size']         =   1400000;
            $config['upload_path']      = 	"files/ticket";

            //Load upload library and initialize configuration
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $result     =   $this->upload->do_upload('file');

            if (!$result) {
                return $this->upload->display_errors('', '');
            }
            else {
                $this->db->set('ticket_id', $ticket_id)->set('filename', $filename)->insert('ticket_attachment');
                return TRUE;
            }
        }

        function get_attachment($ticket_id) {
            return $this->db->where('ticket_id',$ticket_id)->get('ticket_attachment')->result_array();
        }

        function install() {

            $this->db->query('CREATE TABLE `tbl_ticket` (
              `pid` varchar(50) NOT NULL,
              `user_id` int(11) NOT NULL,
              `parent_id` bigint(20) DEFAULT NULL,
              `department_id` int(11) DEFAULT NULL,
              `subject` varchar(100) COLLATE utf8_bin DEFAULT NULL,
              `text` text COLLATE utf8_bin NOT NULL,
              `priority` tinyint(1) COLLATE utf8_bin DEFAULT NULL,
              `is_admin` tinyint(1) COLLATE utf8_bin NOT NULL DEFAULT 0,
              `admin_id` int(10) COLLATE utf8_bin NULL,
              `is_main` tinyint(1) NOT NULL DEFAULT 0,
              `seen` tinyint(1) NOT NULL DEFAULT 0,
              `status` tinyint(1) NOT NULL DEFAULT 0,
              `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              `date_created` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
            
            ALTER TABLE `tbl_ticket`
              ADD PRIMARY KEY (`pid`);');

            $this->db->query('
            CREATE TABLE `tbl_ticket_department` (
              `pid` int(11) NOT NULL,
              `title` varchar(50) COLLATE utf8_bin NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
          
            ALTER TABLE `tbl_ticket_department`
              ADD PRIMARY KEY (`pid`);
      
            ALTER TABLE `tbl_ticket_department`
              MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;');

            $this->db->query('CREATE TABLE `tbl_ticket_attachment` ( `pid` VARCHAR(50) NOT NULL , `ticket_id` INT NOT NULL , `filename` VARCHAR(100) NOT NULL , PRIMARY KEY (`pid`)) ENGINE = InnoDB; ');

        }

        private	function generate_ticket_id($user_id) {

            $id = uniqid();

            while($this->db->where('user_id',$user_id)->get('ticket')->num_rows() == 0) {

            }

            return $id;

        }
    
    }