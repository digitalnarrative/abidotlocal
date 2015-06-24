<?php
 
class al_reply_from_email {
        
        private $reply_line_text;
    
        function __construct() {
            $this->hooks();
            $this->settings = array(
			'server_email' => 'abilocal.reply@zarcode.net',
			'server_login' => 'abilocal.reply@zarcode.net',
			'server_pass' => ';L3(T@2gxVTv',
			'server_url' => 'cpanel05.beotel.net',
			'server_protocol' => 'POP3',
			'server_port' => '110',
			'secure' =>  false
		    );
            $this->reply_line_text  = "======== Reply to this by entering your response in one line via email ========
            
        ";
        
           //class auto load function.
           spl_autoload_register(  array($this,'autoload')  ); 
        
         
        }
    
        function hooks() {
            add_filter( 'bp_init', array($this,"init") );
            add_filter( 'wp_mail_from', array($this,"wp_mail_from") );
            add_filter('wp_mail_from_name', array($this,"new_mail_from_name"));
            	    
	    //bbp reply
	    add_filter("bbp_subscription_mail_message",array($this,"modify_message_mail_body"));
	    add_filter("bbp_pre_notify_subscribers",array($this,"bbp_pre_notify_subscribers"),3,3);
	    add_action("bbp_post_notify_subscribers",array($this,"remove_message_id"));
	    
            
        //    add_action("abi_in_per_one_minute_hook",array($this,"check_replies"));
         //   add_action("abi_fively_cron_hook",array($this,"check_replies")); //forbackup if 1 min one crashed
            
        }
    
        function init() {
        
            if(@$_GET["sync_reply"]=="1"){     
               // $conne = $this->open_mailbox_connection();
                $this->check_replies();
              //  var_dump($conne);
            //    $this->check_replies();
             }
             
             
        }
    
    
        public static function autoload( $class ) {
            // We're only interested in autoloading Horde includes.
            if ( 0 !== strpos( $class, 'Horde' ) ) {
                return;
            }

            $filename = str_replace( array( '_', '\\' ), '/', $class );
            $filename = dirname(__FILE__) . '/' . $filename . '.php';
 
            if ( file_exists( $filename ) ) {
                require_once( $filename );
            }
        }
    
        function wp_mail_from() {
            return $this->settings["server_email"];
        }
        
        function new_mail_from_name($old) {
            return get_bloginfo("name");
        }
    
        function check_replies() {
         
            $this->connection = $this->open_mailbox_connection();
           /// var_dump($this->connection);
            
            if ( ! $this->connection ) {
                return false;
            }
            
            //print_r($this->connection);
            
            $uids = $this->get_messages();
            //print_r($uids);
           // echo '1';
            if ( 0 === sizeof( $uids ) ) {
                $this->connection->shutdown();
                return false;
            }
            
            $debug_info = "";
            
            foreach($uids as $uid) {
                
                $debug_info = get_option("reply_by_mail_debug")."
                ----------------- 
                uid :- $uid
                ";
                update_option("reply_by_mail_debug",$debug_info);
                
                $uid = new Horde_Imap_Client_Ids( $uid );
                $headers = $this->get_message_headers( $uid );
                $from_email = trim($this->get_message_author( $headers ));
                $content = $this->get_message_body( $uid );
                $to = (array) explode(",",$headers->getValue("To"));
               // $identity = $headers->getValue("Delivered-To");
                $identity = $headers->getValue("References");
                $identity = str_replace(array("<",">"),"",$identity);
		
		
                foreach($to as $t) {
                    $t = trim($t);
                    if(strpos($t, "+")) {
                        $identity = $t;
                    }
                }
                
                $debug_info = get_option("reply_by_mail_debug")."
                content :- $content
                identity :- ". var_export($identity, true) ."
                to :- ". var_export($to, true) ."
                ";
                update_option("reply_by_mail_debug",$debug_info);
            
                $identity = explode("@",$identity);
		
		
               // $identity = explode("+",$identity[0]);
                $identity = explode("-",$identity[0]);
                @list($isvalid,$component,$objID) = $identity;
		
                $objID = (int) trim($objID);
		
		    
		//echo $objID;
		//exit;
		
                //echo $isvalid;
                if($isvalid == "reply") {
                    $content = str_replace(array("<br>","<br/>","<br />"),"\n<br>",$content);
                    $content = explode("\n",$content);
                    $new_content = array();               
                    $get_addable_content = "";
                    $i = 0;
                    foreach($content as $c) {
                        $c = trim($c); $c = strip_tags($c);
                        if(strpos($c, '========') !== false) {
                            unset($new_content[count($new_content)-1]);
                            unset($new_content[count($new_content)-2]); 
                            break;
                        }
                        $new_content[] = $c;  
                        $i++;
                    }
                    
                    $debug_info = get_option("reply_by_mail_debug")."
                    get_addable_content :- ". var_export($get_addable_content, true) ."
                    ";
                    update_option("reply_by_mail_debug",$debug_info);
                     
                    $get_addable_content = implode("
",$new_content);
		    
		    
                        
                    if(empty($get_addable_content) || empty($objID)) { //skip and mark it as read.
                        $debug_info = get_option("reply_by_mail_debug")."
                        fail :- ". var_export($objID, true) ."
                        ";
                        update_option("reply_by_mail_debug",$debug_info);
                        $this->mark_as_read($uid);
                        continue;   
                    }
                       
                    //verify if user is valid.
                    $user = get_user_by( "email", $from_email );
                     
		       
                    if(empty($user)) { //skip and mark it as read.
                        $this->mark_as_read($uid);
                        continue;   
                    }
                     
		        
                    if($component == "forum"){
			$topic_id = $objID;
			$this->reply_topic($user,$topic_id,$get_addable_content);
		    }
                 
            
                    $this->mark_as_read($uid);
                                     
                } else { // not valid reply type mark it as read.
                       $this->mark_as_read($uid);
                }                
            }
            
            
        }
    
      
	
	function reply_topic($user,$topic_id,$content) {
		
		$reply_data = array(
			'post_parent'    => $topic_id, // topic ID
			'post_status'    => bbp_get_public_status_id(),
			'post_type'      => bbp_get_reply_post_type(),
			'post_author'    => $user->ID,
			'post_password'  => '',
			'post_content'   => $content,
			'post_title'     => '',
			'menu_order'     => 0,
			'comment_status' => 'closed'
		);
		
		$reply = bbp_insert_reply( $reply_data );
		$topic = get_post($topic_id);
		add_filter("bbp_get_reply_url",array($this,"bbp_get_reply_url"),2,2);
		
		bbp_notify_topic_subscribers($reply, $topic_id , $topic->post_parent ); //send notification
		
		remove_filter("bbp_get_reply_url",array($this,"bbp_get_reply_url"),2,2);
		
		return true;
	}
    
    
	function bbp_get_reply_url($url,$reply_id) {
		$reply = get_post($reply_id);
		return get_permalink($reply->post_parent);
		//return get_bloginfo("url")."?p=".$reply_id;
	}
    
        protected function open_mailbox_connection() {
            $settings = $this->settings;
            
            $options = array(
                'username' => $settings['server_login'],
                'password' => $settings['server_pass'],
                'hostspec' => $settings['server_url'],
                'port' => $settings['server_port'],
                'secure' => $settings['secure'] ? 'ssl' : false,
		     );
            
            if ( 'POP3' == $settings['server_protocol'] ) {
                $this->protocol = 'POP3';
                $connection = new Horde_Imap_Client_Socket_Pop3( $options );
            } else {  // IMAP
                $this->protocol = 'IMAP';
                $connection = new Horde_Imap_Client_Socket( $options );
            }
            $connection->_setInit( 'authmethod', 'USER' );

            try {
                $connection->login();
            }
            catch( Horde_Imap_Client_Exception $e ) {
                 echo $e->getMessage();
                 return false;
            }

            return $connection;
	   }
    
    /**
         * Retrieve the list of new message IDs from the server.
         *
         * @since    1.0.0
         *
         * @return   array    Array of message UIDs
         */
        protected function get_messages() {
            if ( ! $this->connection )
                return;

            try {
                // POP3 doesn't understand about read/unread messages
                if ( 'POP3' == $this->protocol ) {
                    $test = $this->connection->search( 'INBOX' );
                } else {
                    $search_query = new Horde_Imap_Client_Search_Query();
                    $search_query->flag( Horde_Imap_Client::FLAG_SEEN, false );
                    $test = $this->connection->search( 'INBOX', $search_query );
                }
                $uids = $test['match'];
            }
            catch( Horde_Imap_Client_Exception $e ) {
                return false;
            }
            return $uids;
        }
    
        protected function get_message_author( $headers ) {
            // Set the author using the email address (From or Reply-To, the last used)
            $author = $headers->getValue( 'From' );
            // $replyto = $headers->getValue( 'Reply-To' );  // this is not used and doesn't make sense

            if ( preg_match( '|[a-z0-9_.-]+@[a-z0-9_.-]+(?!.*<)|i', $author, $matches ) )
                $author = $matches[0];
            else
                $author = trim( $author );

            $author = sanitize_email( $author );

            if ( is_email( $author ) ) {
                return $author;
            }

            return false;  // author not found
        }
    
        protected function get_message_headers( $uid ) {
            $headerquery = new Horde_Imap_Client_Fetch_Query();
            $headerquery->headerText( array() );
            $headerlist = $this->connection->fetch( 'INBOX', $headerquery, array(
                    'ids' => $uid,
                )
            );

            $headers = $headerlist->first()->getHeaderText( 0, Horde_Imap_Client_Data_Fetch::HEADER_PARSE );
            return $headers;
        }
    
        protected function get_message_body( $uid ) {
            $query = new Horde_Imap_Client_Fetch_Query();
            $query->structure();

            $list = $this->connection->fetch( 'INBOX', $query, array(
                    'ids' => $uid,
                )
            );

            $part = $list->first()->getStructure();
            $body_id = $part->findBody('html');
            if ( is_null( $body_id ) ) {
                $body_id = $part->findBody();
            }
            $body = $part->getPart( $body_id );

            $query2 = new Horde_Imap_Client_Fetch_Query();
            $query2->bodyPart( $body_id, array(
                    'decode' => true,
                    'peek' => true,
                )
            );

            $list2 = $this->connection->fetch( 'INBOX', $query2, array(
                    'ids' => $uid,
                )
            );

            $message2 = $list2->first();
            $content = $message2->getBodyPart( $body_id );
            if ( ! $message2->getBodyPartDecode( $body_id ) ) {
                // Quick way to transfer decode contents
                $body->setContents( $content );
                $content = $body->getContents();
            }

            $content = strip_tags( $content, '<img><p><br><i><b><u><em><strong><strike><font><span><div><style><a>' );
            $content = trim( $content );

            // encode to UTF-8; this fixes up unicode characters like smart quotes, accents, etc.
            $charset = $body->getCharset();
            if ( 'iso-8859-1' == $charset ) {
                $content = utf8_encode( $content );
            } elseif ( function_exists( 'iconv' ) ) {
                $content = iconv( $charset, 'UTF-8', $content );
            }

            return $content;
        }
    
    
        protected function mark_as_read( $uids ) {
            if ( ! $this->connection )
                return;

            $flag = Horde_Imap_Client::FLAG_SEEN;
            $flag_delete = Horde_Imap_Client::FLAG_DELETED;

            try {
                $this->connection->store( 'INBOX', array(
                        'add' => array( $flag ),
                        'ids' => $uids,
                    )
                );
                //flag delete if we can possible.
                $this->connection->store( 'INBOX', array(
                        'add' => array( $flag_delete ),
                        'ids' => $uids,
                    )
                );
            }
            catch ( Horde_Imap_Client_Exception $e ) {
                return false; 
            }
        }

        
        function add_message_id($id) {
             global $reply_by_emai_mid;
             $reply_by_emai_mid = $id;
             add_action( 'phpmailer_init', array($this,"phpmailer_message_id") );   
        }
    
	function remove_message_id() {
             global $reply_by_emai_mid;
             remove_action( 'phpmailer_init', array($this,"phpmailer_message_id") );   
        }
    
        function phpmailer_message_id( &$phpmailer ) { 
            global $reply_by_emai_mid;
            $phpmailer->MessageID = $reply_by_emai_mid;
	    $email_domain = explode("@",$this->settings["server_email"]);
	    $email_domain = $email_domain[1];
        //    $phpmailer->addCustomHeader("reference",  "<".$reply_by_emai_mid."@zarcode3.net>");
            $phpmailer->addCustomHeader("References",  "<".$reply_by_emai_mid."@$email_domain>");
            $phpmailer->addCustomHeader("Message-ID", "<".$reply_by_emai_mid."@$email_domain>");
	    
	    //ID on Email
            //$email = explode("@",$this->settings["server_email"]);
            //$email =  $email[0].'+'.$reply_by_emai_mid.'@'.$email[1];
            //$phpmailer->addCustomHeader("Reply-To", $email);
        }
    
        function modify_message_mail_body($email_content) {
            $email_content = $this->reply_line_text.$email_content;
            return $email_content;
        }
     
	/* Hooking ID into the bbpress mail */
	function bbp_pre_notify_subscribers($reply_id, $topic_id, $user_ids) {
		$this->add_message_id("reply-forum-".$topic_id);
	}
	
     
}

?>